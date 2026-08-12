<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pdo = new PDO(
    'mysql:host=' . env('DB_MASTER_HOST') . ';port=' . env('DB_MASTER_PORT') . ';dbname=' . env('DB_MASTER_DATABASE'),
    env('DB_MASTER_USERNAME'),
    env('DB_MASTER_PASSWORD')
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Search by NAWALHUDAYA or MUHAMAD NAJMA
$searches = ['%NAWALHUDAYA%', '%MUHAMAD NAJMA%', '%MUHAMAD%NAJMA%'];
$target = null;

foreach ($searches as $kw) {
    $stmt = $pdo->prepare("SELECT id, name, saldo, deleted_at FROM students WHERE name LIKE ? LIMIT 5");
    $stmt->execute([$kw]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Search '{$kw}': " . count($results) . " results\n";
    foreach ($results as $s) {
        echo "  ID: {$s['id']} | Name: {$s['name']} | Saldo: {$s['saldo']} | Del: " . ($s['deleted_at'] ?: 'NO') . "\n";
        if (!$target && !$s['deleted_at']) $target = $s;
    }
}

if (!$target) {
    echo "\nNo active student found. Checking local SQLite...\n";
    // Check local
    $localPdo = new PDO('sqlite:database/local_replica.sqlite');
    $stmt = $localPdo->prepare("SELECT id, name, saldo FROM students WHERE name LIKE '%NAWALHUDAYA%' OR name LIKE '%MUHAMAD NAJMA%' LIMIT 5");
    $stmt->execute();
    $localResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Local results:\n";
    foreach ($localResults as $s) {
        echo "  ID: {$s['id']} | Name: {$s['name']} | Saldo: {$s['saldo']}\n";
        
        // Check if this ID exists on VPS
        $stmt2 = $pdo->prepare("SELECT id, name, saldo FROM students WHERE id = ?");
        $stmt2->execute([$s['id']]);
        $vpsMatch = $stmt2->fetch(PDO::FETCH_ASSOC);
        if ($vpsMatch) {
            echo "  VPS match: Name: {$vpsMatch['name']} | Saldo: {$vpsMatch['saldo']}\n";
            $target = $vpsMatch;
        }
    }
}

if (!$target) {
    echo "\nSTILL NOT FOUND. Exiting.\n";
    exit(1);
}

$studentId = $target['id'];
echo "\n============================\n";
echo "AUDITING: {$target['name']} (ID: {$studentId})\n";
echo "Current DB saldo: {$target['saldo']}\n";
echo "============================\n\n";

// Get ALL saldo histories chronologically
$stmt = $pdo->prepare("
    SELECT id, created_at, type, amount, balance_before, balance_after, status, description
    FROM saldo_histories 
    WHERE student_id = ? AND status = 'SUCCESS' AND deleted_at IS NULL
    ORDER BY created_at ASC, id ASC
");
$stmt->execute([$studentId]);
$histories = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Total SUCCESS records: " . count($histories) . "\n\n";

$runningBalance = 0;
$mismatches = 0;

foreach ($histories as $i => $h) {
    $amount = (float)$h['amount'];
    $calcBefore = $runningBalance;
    
    if ($h['type'] === 'IN') {
        $runningBalance += $amount;
    } else {
        $runningBalance -= $amount;
    }
    $calcAfter = $runningBalance;
    
    $dbBefore = (float)$h['balance_before'];
    $dbAfter = (float)$h['balance_after'];
    
    $match = (abs($dbBefore - $calcBefore) < 0.01 && abs($dbAfter - $calcAfter) < 0.01) ? "OK" : "MISS";
    if ($match !== "OK") $mismatches++;
    
    // Only print last 20 rows or mismatches
    if ($match !== "OK" || $i >= count($histories) - 20) {
        echo str_pad($i+1, 3) . " | " . $h['created_at'] . " | " . $h['type'] . " | " . 
             str_pad($amount, 8) . " | DB(" . $dbBefore . "->" . $dbAfter . ") | CALC(" . $calcBefore . "->" . $calcAfter . ") | " . $match . "\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Total: " . count($histories) . " | Mismatches: {$mismatches}\n";
echo "Calc final: {$runningBalance} | DB saldo: {$target['saldo']}\n";

// FIX
if ($mismatches > 0) {
    echo "\n=== FIXING {$mismatches} RECORDS ===\n";
    $runningBalance2 = 0;
    $fixed = 0;
    
    foreach ($histories as $h) {
        $amount = (float)$h['amount'];
        $calcBefore = $runningBalance2;
        if ($h['type'] === 'IN') { $runningBalance2 += $amount; } else { $runningBalance2 -= $amount; }
        $calcAfter = $runningBalance2;
        
        if (abs((float)$h['balance_before'] - $calcBefore) > 0.01 || abs((float)$h['balance_after'] - $calcAfter) > 0.01) {
            $upd = $pdo->prepare("UPDATE saldo_histories SET balance_before = ?, balance_after = ? WHERE id = ?");
            $upd->execute([$calcBefore, $calcAfter, $h['id']]);
            $fixed++;
        }
    }
    
    $upd2 = $pdo->prepare("UPDATE students SET saldo = ? WHERE id = ?");
    $upd2->execute([$runningBalance2, $studentId]);
    
    echo "Fixed {$fixed} saldo_histories rows.\n";
    echo "Student saldo updated: {$target['saldo']} -> {$runningBalance2}\n";
    echo "DONE! Refresh browser now.\n";
} else {
    echo "All records are correct.\n";
}

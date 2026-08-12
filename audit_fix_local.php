<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pdo = new PDO('sqlite:database/local_replica.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Both NAJMA IDs from local
$ids = [
    '41b2044f-7871-4e50-a56e-7b7f3ab020a3',
    '67633690-b872-4ae1-a4e0-da672ff4d637',
];

foreach ($ids as $studentId) {
    $stmt = $pdo->prepare("SELECT id, name, saldo FROM students WHERE id = ?");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$student) { echo "ID {$studentId} not found\n"; continue; }

    echo "\n============================\n";
    echo "STUDENT: {$student['name']} (ID: {$studentId})\n";
    echo "Current saldo: {$student['saldo']}\n";
    echo "============================\n";

    $stmt = $pdo->prepare("
        SELECT id, created_at, type, amount, balance_before, balance_after, status, description
        FROM saldo_histories 
        WHERE student_id = ? AND status = 'SUCCESS' AND deleted_at IS NULL
        ORDER BY created_at ASC, id ASC
    ");
    $stmt->execute([$studentId]);
    $histories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Total SUCCESS records: " . count($histories) . "\n\n";

    if (count($histories) == 0) continue;

    $runningBalance = 0;
    $mismatches = 0;

    foreach ($histories as $i => $h) {
        $amount = (float)$h['amount'];
        $calcBefore = $runningBalance;
        if ($h['type'] === 'IN') { $runningBalance += $amount; } else { $runningBalance -= $amount; }
        $calcAfter = $runningBalance;
        
        $dbBefore = (float)$h['balance_before'];
        $dbAfter = (float)$h['balance_after'];
        
        $match = (abs($dbBefore - $calcBefore) < 0.01 && abs($dbAfter - $calcAfter) < 0.01) ? "OK" : "MISS";
        if ($match !== "OK") $mismatches++;
        
        $desc = substr($h['description'], 0, 35);
        echo str_pad($i+1, 3) . " | " . $h['created_at'] . " | " . str_pad($h['type'], 3) . " | " . 
             str_pad($amount, 8) . " | DB(" . str_pad($dbBefore, 8) . "->" . str_pad($dbAfter, 8) . ") CALC(" . 
             str_pad($calcBefore, 8) . "->" . str_pad($calcAfter, 8) . ") " . $match . " | " . $desc . "\n";
    }

    echo "\nSUMMARY: Total=" . count($histories) . " Mismatches={$mismatches} CalcFinal={$runningBalance} DBsaldo={$student['saldo']}\n";

    // FIX
    if ($mismatches > 0) {
        echo "\n--- FIXING {$mismatches} records ---\n";
        $rb = 0;
        $fixed = 0;
        foreach ($histories as $h) {
            $amount = (float)$h['amount'];
            $cb = $rb;
            if ($h['type'] === 'IN') { $rb += $amount; } else { $rb -= $amount; }
            $ca = $rb;
            if (abs((float)$h['balance_before'] - $cb) > 0.01 || abs((float)$h['balance_after'] - $ca) > 0.01) {
                $upd = $pdo->prepare("UPDATE saldo_histories SET balance_before = ?, balance_after = ? WHERE id = ?");
                $upd->execute([$cb, $ca, $h['id']]);
                $fixed++;
            }
        }
        $upd2 = $pdo->prepare("UPDATE students SET saldo = ? WHERE id = ?");
        $upd2->execute([$rb, $studentId]);
        echo "Fixed {$fixed} rows. Saldo: {$student['saldo']} -> {$rb}\n";
    }
}
echo "\nDONE. Refresh browser (Ctrl+F5).\n";

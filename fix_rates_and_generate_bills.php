<?php

$host = '103.193.179.146';
$db   = 'devctdb';
$user = 'devctdb';
$pass = '@PK@nkm0811';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_TIMEOUT            => 30,
];

// Helper to generate UUID v4
function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->beginTransaction();

    echo "=== TAHAP 1: FIXING MASTER DATA & PAYMENT RATES (TA 2026/2027) ===\n";

    $ay2026Id = '52f11471-6fb0-46ec-91d9-2a3998038232';
    $defaultBillItemId = '087785aa-d554-417d-96de-9185068c7979';
    
    // Fetch all active classrooms
    $stmt = $pdo->query("SELECT id FROM classrooms WHERE deleted_at IS NULL");
    $allClassrooms = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Found " . count($allClassrooms) . " active classrooms for mapping.\n";

    // -------------------------------------------------------------------------
    // A. SYAHRIAH (Target: 12 bulan @ Rp 500.000 = Rp 6.000.000)
    // -------------------------------------------------------------------------
    echo "\n1. Processing Syahriah Master Rates...\n";
    $stmt = $pdo->prepare("SELECT id, name FROM bill_types WHERE name LIKE '%SYAHRIAH%' AND academic_year_id = ? AND deleted_at IS NULL ORDER BY created_at ASC");
    $stmt->execute([$ay2026Id]);
    $syahriahBt = $stmt->fetch();
    
    if (!$syahriahBt) {
        $syahriahBtId = generate_uuid();
        $stmtInsBt = $pdo->prepare("INSERT INTO bill_types (id, bill_item_id, academic_year_id, name, type, payment_input_type, created_at, updated_at) VALUES (?, ?, ?, 'SYAHRIAH', 'MONTHLY', 'FIX', NOW(), NOW())");
        $stmtInsBt->execute([$syahriahBtId, $defaultBillItemId, $ay2026Id]);
        echo "Created missing BillType SYAHRIAH for TA 2026/2027 (ID: $syahriahBtId)\n";
    } else {
        $syahriahBtId = $syahriahBt['id'];
        echo "Found BillType SYAHRIAH for TA 2026/2027 (ID: $syahriahBtId)\n";
    }

    // Get PaymentRates for Syahriah
    $stmtPr = $pdo->prepare("SELECT id FROM payment_rates WHERE bill_type_id = ? AND deleted_at IS NULL ORDER BY created_at ASC");
    $stmtPr->execute([$syahriahBtId]);
    $syahRates = $stmtPr->fetchAll(PDO::FETCH_COLUMN);

    if (empty($syahRates)) {
        $syahRateId = generate_uuid();
        $stmtInsPr = $pdo->prepare("INSERT INTO payment_rates (id, bill_type_id, amount, type, created_at, updated_at) VALUES (?, ?, 6000000, 'REGULAR', NOW(), NOW())");
        $stmtInsPr->execute([$syahRateId, $syahriahBtId]);
        echo "Created PaymentRate for SYAHRIAH (ID: $syahRateId)\n";
    } else {
        $syahRateId = $syahRates[0];
        // Soft delete extra duplicate rates if any
        if (count($syahRates) > 1) {
            $extraRates = array_slice($syahRates, 1);
            $inClause = implode(',', array_fill(0, count($extraRates), '?'));
            $pdo->prepare("UPDATE payment_rates SET deleted_at = NOW() WHERE id IN ($inClause)")->execute($extraRates);
            echo "Cleaned up " . count($extraRates) . " duplicate Syahriah rates.\n";
        }
        $pdo->prepare("UPDATE payment_rates SET amount = 6000000, type = 'REGULAR', updated_at = NOW() WHERE id = ?")->execute([$syahRateId]);
        echo "Updated PaymentRate SYAHRIAH (ID: $syahRateId) to Rp 6.000.000\n";
    }

    // Re-populate Syahriah payment_rate_items (12 months @ 500k)
    $pdo->prepare("DELETE FROM payment_rate_items WHERE payment_rate_id = ?")->execute([$syahRateId]);
    $syahriahMonths = [
        ['m' => 7,  'y' => 2026], ['m' => 8,  'y' => 2026], ['m' => 9,  'y' => 2026],
        ['m' => 10, 'y' => 2026], ['m' => 11, 'y' => 2026], ['m' => 12, 'y' => 2026],
        ['m' => 1,  'y' => 2027], ['m' => 2,  'y' => 2027], ['m' => 3,  'y' => 2027],
        ['m' => 4,  'y' => 2027], ['m' => 5,  'y' => 2027], ['m' => 6,  'y' => 2027],
    ];

    $syahRateItemMap = []; // "m_y" => item_id
    $stmtInsItem = $pdo->prepare("INSERT INTO payment_rate_items (id, payment_rate_id, month, year, amount, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    foreach ($syahriahMonths as $sm) {
        $itemId = generate_uuid();
        $stmtInsItem->execute([$itemId, $syahRateId, $sm['m'], $sm['y'], 500000]);
        $syahRateItemMap[$sm['m'] . '_' . $sm['y']] = $itemId;
    }
    echo "Inserted 12 Syahriah rate items @ Rp 500.000 each.\n";

    // Map Syahriah to classrooms
    $pdo->prepare("DELETE FROM payment_rate_classrooms WHERE payment_rate_id = ?")->execute([$syahRateId]);
    $stmtInsClass = $pdo->prepare("INSERT INTO payment_rate_classrooms (id, payment_rate_id, classroom_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    foreach ($allClassrooms as $cId) {
        $stmtInsClass->execute([generate_uuid(), $syahRateId, $cId]);
    }
    echo "Mapped Syahriah rate to " . count($allClassrooms) . " classrooms.\n";

    // -------------------------------------------------------------------------
    // B. BIAYA APLIKASI (Target: 12 bulan @ Rp 10.000 = Rp 120.000)
    // -------------------------------------------------------------------------
    echo "\n2. Processing Biaya Aplikasi Master Rates...\n";
    $stmt = $pdo->prepare("SELECT id, name FROM bill_types WHERE name LIKE '%APLIKASI%' AND academic_year_id = ? AND deleted_at IS NULL ORDER BY created_at ASC");
    $stmt->execute([$ay2026Id]);
    $aplikasiBt = $stmt->fetch();

    if (!$aplikasiBt) {
        $aplikasiBtId = generate_uuid();
        $pdo->prepare("INSERT INTO bill_types (id, bill_item_id, academic_year_id, name, type, payment_input_type, created_at, updated_at) VALUES (?, ?, ?, 'BIAYA APLIKASI', 'MONTHLY', 'FIX', NOW(), NOW())")->execute([$aplikasiBtId, $defaultBillItemId, $ay2026Id]);
        echo "Created missing BillType BIAYA APLIKASI for TA 2026/2027 (ID: $aplikasiBtId)\n";
    } else {
        $aplikasiBtId = $aplikasiBt['id'];
        echo "Found BillType BIAYA APLIKASI for TA 2026/2027 (ID: $aplikasiBtId)\n";
    }

    $stmtPr = $pdo->prepare("SELECT id FROM payment_rates WHERE bill_type_id = ? AND deleted_at IS NULL ORDER BY created_at ASC");
    $stmtPr->execute([$aplikasiBtId]);
    $appRates = $stmtPr->fetchAll(PDO::FETCH_COLUMN);

    if (empty($appRates)) {
        $appRateId = generate_uuid();
        $pdo->prepare("INSERT INTO payment_rates (id, bill_type_id, amount, type, created_at, updated_at) VALUES (?, ?, 120000, 'REGULAR', NOW(), NOW())")->execute([$appRateId, $aplikasiBtId]);
        echo "Created PaymentRate for BIAYA APLIKASI (ID: $appRateId)\n";
    } else {
        $appRateId = $appRates[0];
        if (count($appRates) > 1) {
            $extraRates = array_slice($appRates, 1);
            $inClause = implode(',', array_fill(0, count($extraRates), '?'));
            $pdo->prepare("UPDATE payment_rates SET deleted_at = NOW() WHERE id IN ($inClause)")->execute($extraRates);
            echo "Cleaned up " . count($extraRates) . " duplicate Biaya Aplikasi rates.\n";
        }
        $pdo->prepare("UPDATE payment_rates SET amount = 120000, type = 'REGULAR', updated_at = NOW() WHERE id = ?")->execute([$appRateId]);
        echo "Updated PaymentRate BIAYA APLIKASI (ID: $appRateId) to Rp 120.000\n";
    }

    // Re-populate Biaya Aplikasi payment_rate_items (12 months @ 10k)
    $pdo->prepare("DELETE FROM payment_rate_items WHERE payment_rate_id = ?")->execute([$appRateId]);
    $appRateItemMap = [];
    foreach ($syahriahMonths as $am) {
        $itemId = generate_uuid();
        $stmtInsItem->execute([$itemId, $appRateId, $am['m'], $am['y'], 10000]);
        $appRateItemMap[$am['m'] . '_' . $am['y']] = $itemId;
    }
    echo "Inserted 12 Biaya Aplikasi rate items @ Rp 10.000 each.\n";

    // Map Biaya Aplikasi to classrooms
    $pdo->prepare("DELETE FROM payment_rate_classrooms WHERE payment_rate_id = ?")->execute([$appRateId]);
    foreach ($allClassrooms as $cId) {
        $stmtInsClass->execute([generate_uuid(), $appRateId, $cId]);
    }
    echo "Mapped Biaya Aplikasi rate to " . count($allClassrooms) . " classrooms.\n";

    // -------------------------------------------------------------------------
    // C. ZARKASI (Target: 6 bulan angsuran: Juli-Nov @ 100k, Des @ 50k = Rp 550.000)
    // -------------------------------------------------------------------------
    echo "\n3. Processing Zarkasi Master Rates...\n";
    $stmt = $pdo->prepare("SELECT id, name FROM bill_types WHERE name LIKE '%ZARKAS%' AND academic_year_id = ? AND deleted_at IS NULL ORDER BY created_at ASC");
    $stmt->execute([$ay2026Id]);
    $zarkasiBt = $stmt->fetch();

    if (!$zarkasiBt) {
        $zarkasiBtId = generate_uuid();
        $pdo->prepare("INSERT INTO bill_types (id, bill_item_id, academic_year_id, name, type, payment_input_type, created_at, updated_at) VALUES (?, ?, ?, 'ZARKASI', 'MONTHLY', 'FIX', NOW(), NOW())")->execute([$zarkasiBtId, $defaultBillItemId, $ay2026Id]);
        echo "Created missing BillType ZARKASI for TA 2026/2027 (ID: $zarkasiBtId)\n";
    } else {
        $zarkasiBtId = $zarkasiBt['id'];
        echo "Found BillType ZARKASI for TA 2026/2027 (ID: $zarkasiBtId)\n";
    }

    $stmtPr = $pdo->prepare("SELECT id FROM payment_rates WHERE bill_type_id = ? AND deleted_at IS NULL ORDER BY created_at ASC");
    $stmtPr->execute([$zarkasiBtId]);
    $zarRates = $stmtPr->fetchAll(PDO::FETCH_COLUMN);

    if (empty($zarRates)) {
        $zarRateId = generate_uuid();
        $pdo->prepare("INSERT INTO payment_rates (id, bill_type_id, amount, type, created_at, updated_at) VALUES (?, ?, 550000, 'REGULAR', NOW(), NOW())")->execute([$zarRateId, $zarkasiBtId]);
        echo "Created PaymentRate for ZARKASI (ID: $zarRateId)\n";
    } else {
        $zarRateId = $zarRates[0];
        if (count($zarRates) > 1) {
            $extraRates = array_slice($zarRates, 1);
            $inClause = implode(',', array_fill(0, count($extraRates), '?'));
            $pdo->prepare("UPDATE payment_rates SET deleted_at = NOW() WHERE id IN ($inClause)")->execute($extraRates);
            echo "Cleaned up " . count($extraRates) . " duplicate Zarkasi rates.\n";
        }
        $pdo->prepare("UPDATE payment_rates SET amount = 550000, type = 'REGULAR', updated_at = NOW() WHERE id = ?")->execute([$zarRateId]);
        echo "Updated PaymentRate ZARKASI (ID: $zarRateId) to Rp 550.000\n";
    }

    // Re-populate Zarkasi payment_rate_items (6 installment items)
    $pdo->prepare("DELETE FROM payment_rate_items WHERE payment_rate_id = ?")->execute([$zarRateId]);
    $zarkasiItems = [
        ['m' => 7,  'y' => 2026, 'amount' => 100000],
        ['m' => 8,  'y' => 2026, 'amount' => 100000],
        ['m' => 9,  'y' => 2026, 'amount' => 100000],
        ['m' => 10, 'y' => 2026, 'amount' => 100000],
        ['m' => 11, 'y' => 2026, 'amount' => 100000],
        ['m' => 12, 'y' => 2026, 'amount' => 50000],
    ];

    $zarRateItemMap = [];
    foreach ($zarkasiItems as $zi) {
        $itemId = generate_uuid();
        $stmtInsItem->execute([$itemId, $zarRateId, $zi['m'], $zi['y'], $zi['amount']]);
        $zarRateItemMap[$zi['m'] . '_' . $zi['y']] = $itemId;
    }
    echo "Inserted 6 Zarkasi installment rate items (Juli-Nov @ 100k, Des @ 50k).\n";

    // Map Zarkasi to classrooms
    $pdo->prepare("DELETE FROM payment_rate_classrooms WHERE payment_rate_id = ?")->execute([$zarRateId]);
    foreach ($allClassrooms as $cId) {
        $stmtInsClass->execute([generate_uuid(), $zarRateId, $cId]);
    }
    echo "Mapped Zarkasi rate to " . count($allClassrooms) . " classrooms.\n";

    // -------------------------------------------------------------------------
    // TAHAP 2: GENERATION & SYNC BILLS FOR ALL ACTIVE STUDENTS
    // -------------------------------------------------------------------------
    echo "\n=== TAHAP 2: GENERATION & SYNC BILLS FOR ACTIVE STUDENTS ===\n";

    $stmt = $pdo->query("SELECT id, classroom_id, name FROM students WHERE deleted_at IS NULL AND (status = 'ACTIVE' OR status IS NULL)");
    $students = $stmt->fetchAll();
    echo "Found " . count($students) . " active students for bill generation.\n";

    $stmtFindBill = $pdo->prepare("
        SELECT id, amount, paid_amount, status 
        FROM bills 
        WHERE student_id = ? AND bill_type_id = ? AND month = ? AND year = ? AND deleted_at IS NULL
    ");

    $stmtUpdateBill = $pdo->prepare("
        UPDATE bills 
        SET amount = ?, payment_rate_item_id = ?, updated_at = NOW() 
        WHERE id = ?
    ");

    $stmtInsertBill = $pdo->prepare("
        INSERT INTO bills 
        (id, bill_type_id, student_id, classroom_id, academic_year_id, month, year, amount, paid_amount, status, payment_rate_item_id, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 'UNPAID', ?, NOW(), NOW())
    ");

    $generatedCount = 0;
    $preservedPaidCount = 0;
    $updatedCount = 0;

    foreach ($students as $st) {
        $stId = $st['id'];
        $cId  = $st['classroom_id'];

        // --- A. SYAHRIAH BILLS (12 months) ---
        foreach ($syahriahMonths as $sm) {
            $m = $sm['m'];
            $y = $sm['y'];
            $rateItemId = $syahRateItemMap["{$m}_{$y}"];

            $stmtFindBill->execute([$stId, $syahriahBtId, $m, $y]);
            $existingBill = $stmtFindBill->fetch();

            if ($existingBill) {
                if ($existingBill['status'] === 'PAID' || $existingBill['paid_amount'] > 0) {
                    // PRESERVE STATUS & PAID AMOUNT!
                    $stmtUpdateBill->execute([500000, $rateItemId, $existingBill['id']]);
                    $preservedPaidCount++;
                    echo "  [PRESERVED PAID] Student: {$st['name']} | Syahriah {$m}/{$y} | Status: {$existingBill['status']} (Paid: Rp " . number_format($existingBill['paid_amount'], 0, ',', '.') . ")\n";
                } else {
                    $stmtUpdateBill->execute([500000, $rateItemId, $existingBill['id']]);
                    $updatedCount++;
                }
            } else {
                $newBillId = generate_uuid();
                $stmtInsertBill->execute([$newBillId, $syahriahBtId, $stId, $cId, $ay2026Id, $m, $y, 500000, $rateItemId]);
                $generatedCount++;
            }
        }

        // --- B. BIAYA APLIKASI BILLS (12 months) ---
        foreach ($syahriahMonths as $am) {
            $m = $am['m'];
            $y = $am['y'];
            $rateItemId = $appRateItemMap["{$m}_{$y}"];

            $stmtFindBill->execute([$stId, $aplikasiBtId, $m, $y]);
            $existingBill = $stmtFindBill->fetch();

            if ($existingBill) {
                if ($existingBill['status'] === 'PAID' || $existingBill['paid_amount'] > 0) {
                    $stmtUpdateBill->execute([10000, $rateItemId, $existingBill['id']]);
                    $preservedPaidCount++;
                    echo "  [PRESERVED PAID] Student: {$st['name']} | Aplikasi {$m}/{$y} | Status: {$existingBill['status']} (Paid: Rp " . number_format($existingBill['paid_amount'], 0, ',', '.') . ")\n";
                } else {
                    $stmtUpdateBill->execute([10000, $rateItemId, $existingBill['id']]);
                    $updatedCount++;
                }
            } else {
                $newBillId = generate_uuid();
                $stmtInsertBill->execute([$newBillId, $aplikasiBtId, $stId, $cId, $ay2026Id, $m, $y, 10000, $rateItemId]);
                $generatedCount++;
            }
        }

        // --- C. ZARKASI BILLS (6 months) ---
        foreach ($zarkasiItems as $zi) {
            $m = $zi['m'];
            $y = $zi['y'];
            $targetAmt = $zi['amount'];
            $rateItemId = $zarRateItemMap["{$m}_{$y}"];

            $stmtFindBill->execute([$stId, $zarkasiBtId, $m, $y]);
            $existingBill = $stmtFindBill->fetch();

            if ($existingBill) {
                if ($existingBill['status'] === 'PAID' || $existingBill['paid_amount'] > 0) {
                    $stmtUpdateBill->execute([$targetAmt, $rateItemId, $existingBill['id']]);
                    $preservedPaidCount++;
                    echo "  [PRESERVED PAID] Student: {$st['name']} | Zarkasi {$m}/{$y} | Status: {$existingBill['status']} (Paid: Rp " . number_format($existingBill['paid_amount'], 0, ',', '.') . ")\n";
                } else {
                    $stmtUpdateBill->execute([$targetAmt, $rateItemId, $existingBill['id']]);
                    $updatedCount++;
                }
            } else {
                $newBillId = generate_uuid();
                $stmtInsertBill->execute([$newBillId, $zarkasiBtId, $stId, $cId, $ay2026Id, $m, $y, $targetAmt, $rateItemId]);
                $generatedCount++;
            }
        }
    }

    $pdo->commit();

    echo "\n=================================================================\n";
    echo "        DATABASE REPAIR & BILL GENERATION COMPLETED!             \n";
    echo "=================================================================\n";
    echo "  - New Bills Generated  : {$generatedCount}\n";
    echo "  - Bills Updated Rate   : {$updatedCount}\n";
    echo "  - PAID Bills Preserved : {$preservedPaidCount}\n\n";

} catch (\PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "ERROR EXECUTING SCRIPT: " . $e->getMessage() . "\n";
}

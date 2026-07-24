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

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->beginTransaction();

    echo "=== AUDIT AND CLEANUP DUPLICATE BILLS FOR SYAHRIAH & BIAYA APLIKASI ===\n";

    $ay2026Id = '52f11471-6fb0-46ec-91d9-2a3998038232';
    $canonicalSyahId = '2d0ec6f2-ef42-47db-9555-aa9cc04234d3';
    $canonicalAppId  = '92201c2f-d5e2-44cb-b28c-4f17be5c3616';

    $stmtStudents = $pdo->query("SELECT id, name FROM students WHERE deleted_at IS NULL");
    $students = $stmtStudents->fetchAll();

    $removedBillsCount = 0;
    $migratedBillsCount = 0;

    foreach ($students as $st) {
        $stId = $st['id'];

        // SYAHRIAH DEDUPLICATION PER STUDENT (12 MONTHS)
        for ($m = 1; $m <= 12; $m++) {
            $year = ($m >= 7) ? 2026 : 2027;

            // Fetch all Syahriah bills for this month for this student across all Syahriah BillTypes
            $stmtFetch = $pdo->prepare("
                SELECT id, bill_type_id, status, paid_amount, amount 
                FROM bills 
                WHERE student_id = ? AND month = ? AND year = ? 
                  AND bill_type_id IN (SELECT id FROM bill_types WHERE (name LIKE '%SYAHRIAH%') AND academic_year_id = ?)
                  AND deleted_at IS NULL
                ORDER BY (status = 'PAID') DESC, (paid_amount > 0) DESC, (bill_type_id = ?) DESC, created_at ASC
            ");
            $stmtFetch->execute([$stId, $m, $year, $ay2026Id, $canonicalSyahId]);
            $monthBills = $stmtFetch->fetchAll();

            if (count($monthBills) > 1) {
                // Keep the first one (most preferred: PAID > paid_amount > canonicalId)
                $keepBill = $monthBills[0];
                $deleteBills = array_slice($monthBills, 1);

                // Check if keepBill has canonicalSyahId; if not, try to update it, or if unique constraint fails, keep existing canonical if any
                if ($keepBill['bill_type_id'] !== $canonicalSyahId) {
                    // Check if canonical bill already exists
                    $stmtCheckCan = $pdo->prepare("SELECT id FROM bills WHERE student_id = ? AND bill_type_id = ? AND month = ? AND year = ? AND deleted_at IS NULL");
                    $stmtCheckCan->execute([$stId, $canonicalSyahId, $m, $year]);
                    $existingCan = $stmtCheckCan->fetch();
                    if ($existingCan) {
                        // Delete keepBill because canonical already exists
                        $pdo->prepare("DELETE FROM bills WHERE id = ?")->execute([$keepBill['id']]);
                        $removedBillsCount++;
                    } else {
                        $pdo->prepare("UPDATE bills SET bill_type_id = ? WHERE id = ?")->execute([$canonicalSyahId, $keepBill['id']]);
                        $migratedBillsCount++;
                    }
                }

                // Delete all other duplicate bills
                foreach ($deleteBills as $dbill) {
                    $pdo->prepare("DELETE FROM bills WHERE id = ?")->execute([$dbill['id']]);
                    $removedBillsCount++;
                }
            } elseif (count($monthBills) == 1) {
                if ($monthBills[0]['bill_type_id'] !== $canonicalSyahId) {
                    $stmtCheckCan = $pdo->prepare("SELECT id FROM bills WHERE student_id = ? AND bill_type_id = ? AND month = ? AND year = ? AND deleted_at IS NULL");
                    $stmtCheckCan->execute([$stId, $canonicalSyahId, $m, $year]);
                    $existingCan = $stmtCheckCan->fetch();
                    if ($existingCan) {
                        $pdo->prepare("DELETE FROM bills WHERE id = ?")->execute([$monthBills[0]['id']]);
                        $removedBillsCount++;
                    } else {
                        $pdo->prepare("UPDATE bills SET bill_type_id = ? WHERE id = ?")->execute([$canonicalSyahId, $monthBills[0]['id']]);
                        $migratedBillsCount++;
                    }
                }
            }
        }

        // BIAYA APLIKASI DEDUPLICATION PER STUDENT (12 MONTHS)
        for ($m = 1; $m <= 12; $m++) {
            $year = ($m >= 7) ? 2026 : 2027;

            $stmtFetch = $pdo->prepare("
                SELECT id, bill_type_id, status, paid_amount, amount 
                FROM bills 
                WHERE student_id = ? AND month = ? AND year = ? 
                  AND bill_type_id IN (SELECT id FROM bill_types WHERE (name LIKE '%APLIKASI%') AND academic_year_id = ?)
                  AND deleted_at IS NULL
                ORDER BY (status = 'PAID') DESC, (paid_amount > 0) DESC, (bill_type_id = ?) DESC, created_at ASC
            ");
            $stmtFetch->execute([$stId, $m, $year, $ay2026Id, $canonicalAppId]);
            $monthBills = $stmtFetch->fetchAll();

            if (count($monthBills) > 1) {
                $keepBill = $monthBills[0];
                $deleteBills = array_slice($monthBills, 1);

                if ($keepBill['bill_type_id'] !== $canonicalAppId) {
                    $stmtCheckCan = $pdo->prepare("SELECT id FROM bills WHERE student_id = ? AND bill_type_id = ? AND month = ? AND year = ? AND deleted_at IS NULL");
                    $stmtCheckCan->execute([$stId, $canonicalAppId, $m, $year]);
                    $existingCan = $stmtCheckCan->fetch();
                    if ($existingCan) {
                        $pdo->prepare("DELETE FROM bills WHERE id = ?")->execute([$keepBill['id']]);
                        $removedBillsCount++;
                    } else {
                        $pdo->prepare("UPDATE bills SET bill_type_id = ? WHERE id = ?")->execute([$canonicalAppId, $keepBill['id']]);
                        $migratedBillsCount++;
                    }
                }

                foreach ($deleteBills as $dbill) {
                    $pdo->prepare("DELETE FROM bills WHERE id = ?")->execute([$dbill['id']]);
                    $removedBillsCount++;
                }
            } elseif (count($monthBills) == 1) {
                if ($monthBills[0]['bill_type_id'] !== $canonicalAppId) {
                    $stmtCheckCan = $pdo->prepare("SELECT id FROM bills WHERE student_id = ? AND bill_type_id = ? AND month = ? AND year = ? AND deleted_at IS NULL");
                    $stmtCheckCan->execute([$stId, $canonicalAppId, $m, $year]);
                    $existingCan = $stmtCheckCan->fetch();
                    if ($existingCan) {
                        $pdo->prepare("DELETE FROM bills WHERE id = ?")->execute([$monthBills[0]['id']]);
                        $removedBillsCount++;
                    } else {
                        $pdo->prepare("UPDATE bills SET bill_type_id = ? WHERE id = ?")->execute([$canonicalAppId, $monthBills[0]['id']]);
                        $migratedBillsCount++;
                    }
                }
            }
        }
    }

    $pdo->commit();

    echo "=== CLEANUP SUCCESSFUL! ===\n";
    echo "  - Redundant Duplicate Bills Removed: $removedBillsCount\n";
    echo "  - Bills Migrated to Canonical ID   : $migratedBillsCount\n";

} catch (\PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "ERROR: " . $e->getMessage() . "\n";
}

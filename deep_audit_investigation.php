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
    PDO::ATTR_TIMEOUT            => 15,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $report = [];
    $report['timestamp'] = date('Y-m-d H:i:s');
    
    // 1. Fetch Academic Years
    $stmt = $pdo->query("SELECT id, name, is_active FROM academic_years ORDER BY created_at DESC");
    $academicYears = $stmt->fetchAll();
    $report['academic_years'] = $academicYears;
    
    // Find Academic Year for 2026/2027
    $ay2026 = null;
    foreach ($academicYears as $ay) {
        if (strpos($ay['name'], '2026') !== false) {
            $ay2026 = $ay;
            break;
        }
    }
    
    // 2. Fetch Bill Types for Syahriah, Aplikasi, Zarkasi
    $stmt = $pdo->prepare("
        SELECT bt.id, bt.name, bt.type, bt.academic_year_id, ay.name as ay_name 
        FROM bill_types bt 
        LEFT JOIN academic_years ay ON bt.academic_year_id = ay.id 
        WHERE (bt.name LIKE '%SYAHR%' OR bt.name LIKE '%APLIKASI%' OR bt.name LIKE '%ZARKAS%')
          AND bt.deleted_at IS NULL
    ");
    $stmt->execute();
    $billTypes = $stmt->fetchAll();
    $report['bill_types'] = $billTypes;
    
    // Categorize Bill Types
    $syahriahBtIds = [];
    $aplikasiBtIds = [];
    $zarkasiBtIds = [];
    
    foreach ($billTypes as $bt) {
        $name = strtoupper($bt['name']);
        if (strpos($name, 'SYAHR') !== false) {
            $syahriahBtIds[] = $bt['id'];
        } elseif (strpos($name, 'APLIKASI') !== false) {
            $aplikasiBtIds[] = $bt['id'];
        } elseif (strpos($name, 'ZARKAS') !== false) {
            $zarkasiBtIds[] = $bt['id'];
        }
    }
    
    // 3. Inspect Payment Rates & Payment Rate Items for these Bill Types
    $stmt = $pdo->query("
        SELECT pr.id as rate_id, pr.bill_type_id, pr.amount as rate_amount, pr.type as rate_type,
               bt.name as bill_type_name, ay.name as ay_name
        FROM payment_rates pr
        JOIN bill_types bt ON pr.bill_type_id = bt.id
        LEFT JOIN academic_years ay ON bt.academic_year_id = ay.id
        WHERE pr.deleted_at IS NULL
          AND (bt.name LIKE '%SYAHR%' OR bt.name LIKE '%APLIKASI%' OR bt.name LIKE '%ZARKAS%')
    ");
    $paymentRates = $stmt->fetchAll();
    
    foreach ($paymentRates as &$pr) {
        $stmtItems = $pdo->prepare("
            SELECT id, month, year, amount 
            FROM payment_rate_items 
            WHERE payment_rate_id = ? 
            ORDER BY year ASC, month ASC
        ");
        $stmtItems->execute([$pr['rate_id']]);
        $pr['items'] = $stmtItems->fetchAll();
        
        $stmtClass = $pdo->prepare("
            SELECT count(*) as total 
            FROM payment_rate_classrooms 
            WHERE payment_rate_id = ? AND deleted_at IS NULL
        ");
        $stmtClass->execute([$pr['rate_id']]);
        $pr['classroom_count'] = $stmtClass->fetch()['total'];
        
        $stmtStudent = $pdo->prepare("
            SELECT count(*) as total 
            FROM payment_rate_students 
            WHERE payment_rate_id = ? AND deleted_at IS NULL
        ");
        $stmtStudent->execute([$pr['rate_id']]);
        $pr['student_count'] = $stmtStudent->fetch()['total'];
    }
    $report['payment_rates'] = $paymentRates;
    
    // 4. Audit Active Students
    $stmt = $pdo->query("
        SELECT s.id, s.nis, s.name, s.gender, s.classroom_id, c.name as classroom_name, sch.name as school_name, sch.type as school_type
        FROM students s
        LEFT JOIN classrooms c ON s.classroom_id = c.id
        LEFT JOIN schools sch ON c.school_id = sch.id
        WHERE s.deleted_at IS NULL
        ORDER BY sch.name, c.name, s.name
    ");
    $students = $stmt->fetchAll();
    $report['total_students'] = count($students);
    
    // 5. Audit Bills for July 2026 to June 2027 (Academic Year 2026/2027)
    // Target Months for 12 months Syahriah & Aplikasi:
    // 7/2026, 8/2026, 9/2026, 10/2026, 11/2026, 12/2026, 1/2027, 2/2027, 3/2027, 4/2027, 5/2027, 6/2027
    
    // Target Months for 6 months Zarkasi:
    // 7/2026 (100k), 8/2026 (100k), 9/2026 (100k), 10/2026 (100k), 11/2026 (100k), 12/2026 (50k)
    
    $expectedZarkasi = [
        ['month' => 7,  'year' => 2026, 'amount' => 100000],
        ['month' => 8,  'year' => 2026, 'amount' => 100000],
        ['month' => 9,  'year' => 2026, 'amount' => 100000],
        ['month' => 10, 'year' => 2026, 'amount' => 100000],
        ['month' => 11, 'year' => 2026, 'amount' => 100000],
        ['month' => 12, 'year' => 2026, 'amount' => 50000],
    ];
    
    $expectedSyahriahMonthly = 500000;
    $expectedAplikasiMonthly = 10000;
    
    // Fetch all existing bills for these category bill types
    $stmt = $pdo->query("
        SELECT b.id, b.bill_type_id, b.student_id, b.academic_year_id, b.month, b.year, b.amount, b.status,
               bt.name as bill_type_name, ay.name as ay_name, s.name as student_name
        FROM bills b
        JOIN bill_types bt ON b.bill_type_id = bt.id
        LEFT JOIN academic_years ay ON b.academic_year_id = ay.id
        JOIN students s ON b.student_id = s.id
        WHERE b.deleted_at IS NULL
          AND (bt.name LIKE '%SYAHR%' OR bt.name LIKE '%APLIKASI%' OR bt.name LIKE '%ZARKAS%')
    ");
    $allBills = $stmt->fetchAll();
    
    // Group bills by student_id and bill category and month/year
    $studentBills = [];
    foreach ($allBills as $b) {
        $stId = $b['student_id'];
        $btName = strtoupper($b['bill_type_name']);
        
        $cat = 'OTHER';
        if (strpos($btName, 'SYAHR') !== false) $cat = 'SYAHRIAH';
        elseif (strpos($btName, 'APLIKASI') !== false) $cat = 'APLIKASI';
        elseif (strpos($btName, 'ZARKAS') !== false) $cat = 'ZARKASI';
        
        $key = $b['year'] . '-' . sprintf('%02d', $b['month']);
        $studentBills[$stId][$cat][$key][] = $b;
    }
    
    // Detailed analysis per student & summary statistics
    $analysis = [
        'syahriah' => [
            'correct_students_count' => 0,
            'students_with_issues' => [],
            'total_expected_per_student' => 6000000,
            'total_expected_all' => count($students) * 6000000,
            'total_bills_count' => 0,
            'total_amount_in_db' => 0,
        ],
        'aplikasi' => [
            'correct_students_count' => 0,
            'students_with_issues' => [],
            'total_expected_per_student' => 120000,
            'total_expected_all' => count($students) * 120000,
            'total_bills_count' => 0,
            'total_amount_in_db' => 0,
        ],
        'zarkasi' => [
            'correct_students_count' => 0,
            'students_with_issues' => [],
            'total_expected_per_student' => 550000,
            'total_expected_all' => count($students) * 550000,
            'total_bills_count' => 0,
            'total_amount_in_db' => 0,
        ]
    ];
    
    // Month lists for 2026/2027 (July 2026 to June 2027)
    $syahriahAplikasiMonths = [
        ['m' => 7,  'y' => 2026],
        ['m' => 8,  'y' => 2026],
        ['m' => 9,  'y' => 2026],
        ['m' => 10, 'y' => 2026],
        ['m' => 11, 'y' => 2026],
        ['m' => 12, 'y' => 2026],
        ['m' => 1,  'y' => 2027],
        ['m' => 2,  'y' => 2027],
        ['m' => 3,  'y' => 2027],
        ['m' => 4,  'y' => 2027],
        ['m' => 5,  'y' => 2027],
        ['m' => 6,  'y' => 2027],
    ];

    foreach ($students as $st) {
        $stId = $st['id'];
        
        // --- AUDIT SYAHRIAH ---
        $syahIssues = [];
        $syahTotal = 0;
        foreach ($syahriahAplikasiMonths as $my) {
            $key = $my['y'] . '-' . sprintf('%02d', $my['m']);
            $billsFound = $studentBills[$stId]['SYAHRIAH'][$key] ?? [];
            if (empty($billsFound)) {
                $syahIssues[] = "Missing bill for period $key";
            } elseif (count($billsFound) > 1) {
                $syahIssues[] = "Duplicate bills for period $key (Count: " . count($billsFound) . ")";
                foreach ($billsFound as $bf) {
                    $syahTotal += $bf['amount'];
                    $analysis['syahriah']['total_amount_in_db'] += $bf['amount'];
                    $analysis['syahriah']['total_bills_count']++;
                }
            } else {
                $b = $billsFound[0];
                $syahTotal += $b['amount'];
                $analysis['syahriah']['total_amount_in_db'] += $b['amount'];
                $analysis['syahriah']['total_bills_count']++;
                if ($b['amount'] != $expectedSyahriahMonthly) {
                    $syahIssues[] = "Incorrect rate for period $key: Rp " . number_format($b['amount'], 0, ',', '.') . " (Expected: Rp " . number_format($expectedSyahriahMonthly, 0, ',', '.') . ")";
                }
            }
        }
        if (empty($syahIssues)) {
            $analysis['syahriah']['correct_students_count']++;
        } else {
            $analysis['syahriah']['students_with_issues'][] = [
                'student_id' => $stId,
                'student_name' => $st['name'],
                'classroom' => $st['classroom_name'],
                'school' => $st['school_name'],
                'total_amount' => $syahTotal,
                'expected_amount' => 6000000,
                'issues' => $syahIssues
            ];
        }

        // --- AUDIT BIAYA APLIKASI ---
        $appIssues = [];
        $appTotal = 0;
        foreach ($syahriahAplikasiMonths as $my) {
            $key = $my['y'] . '-' . sprintf('%02d', $my['m']);
            $billsFound = $studentBills[$stId]['APLIKASI'][$key] ?? [];
            if (empty($billsFound)) {
                $appIssues[] = "Missing bill for period $key";
            } elseif (count($billsFound) > 1) {
                $appIssues[] = "Duplicate bills for period $key (Count: " . count($billsFound) . ")";
                foreach ($billsFound as $bf) {
                    $appTotal += $bf['amount'];
                    $analysis['aplikasi']['total_amount_in_db'] += $bf['amount'];
                    $analysis['aplikasi']['total_bills_count']++;
                }
            } else {
                $b = $billsFound[0];
                $appTotal += $b['amount'];
                $analysis['aplikasi']['total_amount_in_db'] += $b['amount'];
                $analysis['aplikasi']['total_bills_count']++;
                if ($b['amount'] != $expectedAplikasiMonthly) {
                    $appIssues[] = "Incorrect rate for period $key: Rp " . number_format($b['amount'], 0, ',', '.') . " (Expected: Rp " . number_format($expectedAplikasiMonthly, 0, ',', '.') . ")";
                }
            }
        }
        if (empty($appIssues)) {
            $analysis['aplikasi']['correct_students_count']++;
        } else {
            $analysis['aplikasi']['students_with_issues'][] = [
                'student_id' => $stId,
                'student_name' => $st['name'],
                'classroom' => $st['classroom_name'],
                'school' => $st['school_name'],
                'total_amount' => $appTotal,
                'expected_amount' => 120000,
                'issues' => $appIssues
            ];
        }

        // --- AUDIT ZARKASI ---
        $zarIssues = [];
        $zarTotal = 0;
        foreach ($expectedZarkasi as $ez) {
            $key = $ez['year'] . '-' . sprintf('%02d', $ez['month']);
            $billsFound = $studentBills[$stId]['ZARKASI'][$key] ?? [];
            if (empty($billsFound)) {
                $zarIssues[] = "Missing bill for period $key (Expected: Rp " . number_format($ez['amount'], 0, ',', '.') . ")";
            } elseif (count($billsFound) > 1) {
                $zarIssues[] = "Duplicate bills for period $key (Count: " . count($billsFound) . ")";
                foreach ($billsFound as $bf) {
                    $zarTotal += $bf['amount'];
                    $analysis['zarkasi']['total_amount_in_db'] += $bf['amount'];
                    $analysis['zarkasi']['total_bills_count']++;
                }
            } else {
                $b = $billsFound[0];
                $zarTotal += $b['amount'];
                $analysis['zarkasi']['total_amount_in_db'] += $b['amount'];
                $analysis['zarkasi']['total_bills_count']++;
                if ($b['amount'] != $ez['amount']) {
                    $zarIssues[] = "Incorrect rate for period $key: Rp " . number_format($b['amount'], 0, ',', '.') . " (Expected: Rp " . number_format($ez['amount'], 0, ',', '.') . ")";
                }
            }
        }
        // Also check if there are extra Zarkasi bills outside July-Dec 2026
        $allZarBillsForSt = $studentBills[$stId]['ZARKASI'] ?? [];
        foreach ($allZarBillsForSt as $key => $bfList) {
            $parts = explode('-', $key);
            $y = (int)$parts[0];
            $m = (int)$parts[1];
            if ($y !== 2026 || $m < 7 || $m > 12) {
                $zarIssues[] = "Unexpected Zarkasi bill in period $key outside designated July-Dec 2026 schedule";
            }
        }
        
        if (empty($zarIssues)) {
            $analysis['zarkasi']['correct_students_count']++;
        } else {
            $analysis['zarkasi']['students_with_issues'][] = [
                'student_id' => $stId,
                'student_name' => $st['name'],
                'classroom' => $st['classroom_name'],
                'school' => $st['school_name'],
                'total_amount' => $zarTotal,
                'expected_amount' => 550000,
                'issues' => $zarIssues
            ];
        }
    }
    
    $report['audit_analysis'] = $analysis;
    
    file_put_contents(__DIR__.'/deep_audit_result.json', json_encode($report, JSON_PRETTY_PRINT));
    echo "DEEP AUDIT COMPLETED SUCCESSFULLY! Output saved to deep_audit_result.json\n";

} catch (\PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

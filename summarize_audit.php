<?php

$data = json_decode(file_get_contents(__DIR__.'/deep_audit_result.json'), true);

echo "=================================================================\n";
echo "           DEEP DIVE AUDIT & INVESTIGATION REPORT               \n";
echo "=================================================================\n\n";

echo "Timestamp: " . $data['timestamp'] . "\n";
echo "Total Students Audited: " . $data['total_students'] . "\n\n";

echo "--- ACADEMIC YEARS ---\n";
foreach ($data['academic_years'] as $ay) {
    echo "  * {$ay['name']} (ID: {$ay['id']}) - Active: {$ay['is_active']}\n";
}

echo "\n--- BILL TYPES DETECTED ---\n";
foreach ($data['bill_types'] as $bt) {
    echo "  * [{$bt['id']}] {$bt['name']} | Type: {$bt['type']} | Academic Year: {$bt['ay_name']}\n";
}

echo "\n--- PAYMENT RATES & ITEMS DEFINED IN SYSTEM ---\n";
foreach ($data['payment_rates'] as $pr) {
    echo "  * Rate ID: {$pr['rate_id']}\n";
    echo "    Bill Type: {$pr['bill_type_name']} ({$pr['ay_name']})\n";
    echo "    Base Amount: Rp " . number_format($pr['rate_amount'], 0, ',', '.') . " | Type: {$pr['rate_type']}\n";
    echo "    Classrooms Assigned: {$pr['classroom_count']} | Students Assigned: {$pr['student_count']}\n";
    echo "    Rate Items:\n";
    if (empty($pr['items'])) {
        echo "      (None defined)\n";
    } else {
        foreach ($pr['items'] as $item) {
            echo "      - Month {$item['month']}/{$item['year']}: Rp " . number_format($item['amount'], 0, ',', '.') . "\n";
        }
    }
    echo "\n";
}

echo "=================================================================\n";
echo "                  AUDIT ANALYSIS SUMMARY                         \n";
echo "=================================================================\n\n";

$audit = $data['audit_analysis'];

foreach (['syahriah' => '1. SYAHRIAH (Target: Rp 500.000 / bln = Rp 6.000.000 / 12 bln)',
          'aplikasi' => '2. BIAYA APLIKASI (Target: Rp 10.000 / bln = Rp 120.000 / 12 bln)',
          'zarkasi'  => '3. ZARKASI (Target: 6 bln angsuran, Juli-Nov @100k, Des @50k = Rp 550.000)'] as $key => $title) {
    
    $cat = $audit[$key];
    echo "$title\n";
    echo "-----------------------------------------------------------------\n";
    echo "  - Total Students Perfectly Compliant : " . $cat['correct_students_count'] . " / " . $data['total_students'] . "\n";
    echo "  - Total Students with Issues/Gaps   : " . count($cat['students_with_issues']) . "\n";
    echo "  - Expected Total per Student        : Rp " . number_format($cat['total_expected_per_student'], 0, ',', '.') . "\n";
    echo "  - Total Expected across All Students: Rp " . number_format($cat['total_expected_all'], 0, ',', '.') . "\n";
    echo "  - Total Bills Count in Database     : " . $cat['total_bills_count'] . "\n";
    echo "  - Total Bill Amount in Database     : Rp " . number_format($cat['total_amount_in_db'], 0, ',', '.') . "\n\n";

    if (!empty($cat['students_with_issues'])) {
        echo "  [SAMPLE ISSUES DETECTED - FIRST 5 STUDENTS]:\n";
        $sample = array_slice($cat['students_with_issues'], 0, 5);
        foreach ($sample as $st) {
            echo "   * Student: {$st['student_name']} ({$st['classroom']} - {$st['school']})\n";
            echo "     Total Amount Generated: Rp " . number_format($st['total_amount'], 0, ',', '.') . " (Expected: Rp " . number_format($st['expected_amount'], 0, ',', '.') . ")\n";
            echo "     Issues:\n";
            foreach ($st['issues'] as $iss) {
                echo "      - $iss\n";
            }
        }
        echo "\n";
    }
}

<?php

$data = json_decode(file_get_contents(__DIR__.'/deep_audit_result.json'), true);

echo "Total Students: " . $data['total_students'] . "\n\n";

echo "--- ACADEMIC YEARS ---\n";
foreach ($data['academic_years'] as $ay) {
    echo "  * [{$ay['id']}] {$ay['name']} - Active: {$ay['is_active']}\n";
}

echo "\n--- PAYMENT RATES DEFINED IN SYSTEM ---\n";
foreach ($data['payment_rates'] as $pr) {
    echo "  * Rate ID: {$pr['rate_id']}\n";
    echo "    Bill Type: {$pr['bill_type_name']} (AY: {$pr['ay_name']})\n";
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

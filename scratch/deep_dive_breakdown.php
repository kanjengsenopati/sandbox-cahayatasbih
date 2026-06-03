<?php
require 'F:/Antigravity/Projects/cahayatasbih/vendor/autoload.php';
$app = require_once 'F:/Antigravity/Projects/cahayatasbih/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DEEP DIVE: Efficient Query ===\n\n";

// 1. Breakdown per Jenis using raw SQL (no memory issue)
echo "1. Breakdown per BillType with AY (ALL bills, no filter):\n";
$breakdown = DB::select("
    SELECT 
        bt.name as type_name,
        bi.name as unit_name,
        ay.name as year_name,
        b.academic_year_id,
        COUNT(*) as cnt,
        SUM(b.amount) as target,
        SUM(CASE WHEN b.status = 'PAID' THEN b.amount ELSE 0 END) as paid
    FROM bills b
    JOIN bill_types bt ON b.bill_type_id = bt.id
    LEFT JOIN bill_items bi ON bt.bill_item_id = bi.id
    LEFT JOIN academic_years ay ON bt.academic_year_id = ay.id
    WHERE b.deleted_at IS NULL
    GROUP BY bt.name, bi.name, ay.name, b.academic_year_id
    ORDER BY target DESC
");

foreach ($breakdown as $row) {
    echo "  - {$row->type_name} ({$row->unit_name} - {$row->year_name})\n";
    echo "    Bill AY: {$row->academic_year_id}, Count: {$row->cnt}, Target: Rp " . number_format($row->target, 0, ',', '.') . ", Paid: Rp " . number_format($row->paid, 0, ',', '.') . "\n";
}

echo "\n2. KEY CHECK: Does BillType.academic_year_id MATCH Bill.academic_year_id?\n";
$mismatch = DB::select("
    SELECT 
        bt.name as type_name,
        bt.academic_year_id as billtype_ay_id,
        b.academic_year_id as bill_ay_id,
        COUNT(*) as cnt
    FROM bills b
    JOIN bill_types bt ON b.bill_type_id = bt.id
    WHERE b.deleted_at IS NULL
    AND bt.academic_year_id != b.academic_year_id
    GROUP BY bt.name, bt.academic_year_id, b.academic_year_id
    LIMIT 20
");

if (empty($mismatch)) {
    echo "  ALL MATCH - BillType.academic_year_id always equals Bill.academic_year_id\n";
} else {
    echo "  MISMATCHES FOUND:\n";
    foreach ($mismatch as $m) {
        echo "  - Type: {$m->type_name}, BillType AY: {$m->billtype_ay_id}, Bill AY: {$m->bill_ay_id}, Count: {$m->cnt}\n";
    }
}

echo "\n3. Source Breakdown (NO filter):\n";
$sources = DB::select("
    SELECT 
        pm.type as pm_type,
        pm.name as pm_name,
        COUNT(t.id) as cnt,
        SUM(t.pay_amount) as total
    FROM transactions t
    JOIN payment_methods pm ON t.payment_method_id = pm.id
    WHERE t.status = 'PAID'
    AND t.type = 'BILL'
    AND t.deleted_at IS NULL
    GROUP BY pm.type, pm.name
");
foreach ($sources as $s) {
    echo "  - {$s->pm_name} ({$s->pm_type}): Count: {$s->cnt}, Total: Rp " . number_format($s->total, 0, ',', '.') . "\n";
}

echo "\n4. Source Breakdown per AcademicYear (via transactionDetails -> bills):\n";
$sourcesByAY = DB::select("
    SELECT 
        ay.name as ay_name,
        pm.type as pm_type,
        COUNT(DISTINCT t.id) as cnt,
        SUM(td.amount) as total_from_details
    FROM transactions t
    JOIN payment_methods pm ON t.payment_method_id = pm.id
    JOIN transaction_details td ON td.transaction_id = t.id AND td.deleted_at IS NULL
    JOIN bills b ON td.bill_id = b.id AND b.deleted_at IS NULL
    JOIN academic_years ay ON b.academic_year_id = ay.id
    WHERE t.status = 'PAID'
    AND t.type = 'BILL'
    AND t.deleted_at IS NULL
    GROUP BY ay.name, pm.type
    ORDER BY ay.name, pm.type
");
foreach ($sourcesByAY as $s) {
    echo "  - AY: {$s->ay_name}, Source: {$s->pm_type}, TxCount: {$s->cnt}, Total: Rp " . number_format($s->total_from_details, 0, ',', '.') . "\n";
}

echo "\n5. CRITICAL: Check if Controller query has BillType.academicYear scope issue:\n";
echo "   The controller uses: Bill::with(['billType.billItem', 'billType.academicYear'])\n";
echo "   This loads BillType->academicYear, but the bill_query itself uses Bill.academic_year_id\n";
echo "   If academic_year_id filter is NOT sent (Semua Tahun Ajaran), the \$billQuery has NO academic_year_id filter.\n";
echo "   BUT: The breakdown grouping key uses billType->academicYear->name. This is the BillType's AY, not the Bill's AY.\n";
echo "   Let's check if any BillType has a DIFFERENT academic_year than its Bills:\n";

$billTypeAYCheck = DB::select("
    SELECT 
        bt.id,
        bt.name,
        bt.academic_year_id as bt_ay,
        ay_bt.name as bt_ay_name,
        b.academic_year_id as bill_ay,
        ay_b.name as bill_ay_name,
        COUNT(*) as cnt
    FROM bills b
    JOIN bill_types bt ON b.bill_type_id = bt.id
    LEFT JOIN academic_years ay_bt ON bt.academic_year_id = ay_bt.id
    LEFT JOIN academic_years ay_b ON b.academic_year_id = ay_b.id
    WHERE b.deleted_at IS NULL
    GROUP BY bt.id, bt.name, bt.academic_year_id, ay_bt.name, b.academic_year_id, ay_b.name
    ORDER BY bt.name
");
foreach ($billTypeAYCheck as $c) {
    $match = ($c->bt_ay === $c->bill_ay) ? 'MATCH' : 'MISMATCH!';
    echo "  - BillType: {$c->name} | BT AY: {$c->bt_ay_name} | Bill AY: {$c->bill_ay_name} | {$match} | Bills: {$c->cnt}\n";
}

echo "\n6. Check default filter behavior in JS:\n";
echo "   When 'Semua Tahun Ajaran' is selected, academic_year_id should be empty/null.\n";
echo "   When no date filter is applied, start_date and end_date should be empty/null.\n";
echo "   The question: Does the JS actually send empty values, or does it send default values like today's date?\n";

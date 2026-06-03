<?php
require 'F:/Antigravity/Projects/cahayatasbih/vendor/autoload.php';
$app = require_once 'F:/Antigravity/Projects/cahayatasbih/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VALIDATION: New Breakdown Query (Semua Tahun Ajaran, Semua Periode) ===\n\n";

$breakdownRaw = DB::table('bills as b')
    ->join('bill_types as bt', 'b.bill_type_id', '=', 'bt.id')
    ->leftJoin('bill_items as bi', 'bt.bill_item_id', '=', 'bi.id')
    ->leftJoin('academic_years as ay', 'bt.academic_year_id', '=', 'ay.id')
    ->whereNull('b.deleted_at')
    ->select(
        'bt.name as type_name',
        'bi.name as unit_name',
        'ay.name as year_name',
        DB::raw('SUM(b.amount) as target'),
        DB::raw("SUM(CASE WHEN b.status = 'PAID' THEN b.amount ELSE 0 END) as paid")
    )
    ->groupBy('bt.name', 'bi.name', 'ay.name')
    ->orderByDesc('target')
    ->get();

$totalTarget = 0;
$totalPaid = 0;
echo "Breakdown (NO filters):\n";
foreach ($breakdownRaw as $row) {
    $fullName = trim($row->type_name);
    $parts = [];
    if ($row->unit_name) $parts[] = $row->unit_name;
    if ($row->year_name) $parts[] = $row->year_name;
    if (!empty($parts)) {
        $fullName .= ' (' . implode(' - ', $parts) . ')';
    }
    echo "  - $fullName: Target=Rp " . number_format($row->target, 0, ',', '.') . " | Paid=Rp " . number_format($row->paid, 0, ',', '.') . "\n";
    $totalTarget += $row->target;
    $totalPaid += $row->paid;
}
echo "\n  TOTAL Target: Rp " . number_format($totalTarget, 0, ',', '.') . "\n";
echo "  TOTAL Paid: Rp " . number_format($totalPaid, 0, ',', '.') . "\n";

echo "\n=== Source Breakdown (NO filters) ===\n";
$sources = DB::table('transactions as t')
    ->join('payment_methods as pm', 't.payment_method_id', '=', 'pm.id')
    ->where('t.status', 'PAID')
    ->where('t.type', 'BILL')
    ->whereNull('t.deleted_at')
    ->select('pm.type', 'pm.name', DB::raw('SUM(t.pay_amount) as total'), DB::raw('COUNT(t.id) as cnt'))
    ->groupBy('pm.type', 'pm.name')
    ->get();

foreach ($sources as $s) {
    echo "  - {$s->name} ({$s->type}): Count={$s->cnt}, Total=Rp " . number_format($s->total, 0, ',', '.') . "\n";
}

echo "\n=== CROSS-CHECK: AY count in breakdown ===\n";
$ayCount = DB::table('bills as b')
    ->join('bill_types as bt', 'b.bill_type_id', '=', 'bt.id')
    ->leftJoin('academic_years as ay', 'bt.academic_year_id', '=', 'ay.id')
    ->whereNull('b.deleted_at')
    ->select('ay.name')
    ->distinct()
    ->get();

foreach ($ayCount as $a) {
    echo "  AY: " . ($a->name ?? 'NULL') . "\n";
}

echo "\nExpected: BOTH 2024/2025 AND 2025/2026 should appear in breakdown.\n";
echo "If you see only one AY, there's still a filtering issue.\n";

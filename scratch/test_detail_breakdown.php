<?php
require 'F:/Antigravity/Projects/cahayatasbih/vendor/autoload.php';
$app = require_once 'F:/Antigravity/Projects/cahayatasbih/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DETAILED BREAKDOWN UNIQUE SUBQUERY TEST ===\n\n";

// Subquery to get unique bill_id to pm.type mapping
$billPaymentsSub = DB::table('transaction_details as td')
    ->join('transactions as t', 'td.transaction_id', '=', 't.id')
    ->join('payment_methods as pm', 't.payment_method_id', '=', 'pm.id')
    ->where('t.status', 'PAID')
    ->where('t.type', 'BILL')
    ->whereNull('t.deleted_at')
    ->select('td.bill_id', DB::raw('MAX(pm.type) as pm_type'))
    ->groupBy('td.bill_id');

$q = DB::table('bills as b')
    ->join('bill_types as bt', 'b.bill_type_id', '=', 'bt.id')
    ->leftJoin('bill_items as bi', 'bt.bill_item_id', '=', 'bi.id')
    ->leftJoin('academic_years as ay', 'bt.academic_year_id', '=', 'ay.id')
    // Join the subquery
    ->leftJoinSub($billPaymentsSub, 'bp', 'b.id', '=', 'bp.bill_id')
    ->whereNull('b.deleted_at')
    ->select(
        'bt.name as type_name',
        'bi.name as unit_name',
        'ay.name as year_name',
        DB::raw('SUM(b.amount) as target'),
        DB::raw("SUM(CASE WHEN b.status = 'PAID' THEN b.amount ELSE 0 END) as paid"),
        DB::raw("SUM(CASE WHEN b.status = 'PAID' AND bp.pm_type = 'CASH' THEN b.amount ELSE 0 END) as paid_cash"),
        DB::raw("SUM(CASE WHEN b.status = 'PAID' AND bp.pm_type = 'BALANCE' THEN b.amount ELSE 0 END) as paid_balance"),
        DB::raw("SUM(CASE WHEN b.status = 'PAID' AND bp.pm_type NOT IN ('CASH', 'BALANCE') THEN b.amount ELSE 0 END) as paid_transfer")
    )
    ->groupBy('bt.name', 'bi.name', 'ay.name')
    ->orderByDesc('target')
    ->get();

$totalTarget = 0;
$totalPaid = 0;
$totalCash = 0;
$totalBalance = 0;
$totalTransfer = 0;

foreach ($q->take(5) as $row) {
    $fullName = trim($row->type_name);
    $parts = [];
    if ($row->unit_name) $parts[] = $row->unit_name;
    if ($row->year_name) $parts[] = $row->year_name;
    if (!empty($parts)) $fullName .= ' (' . implode(' - ', $parts) . ')';
    
    echo "$fullName:\n";
    echo "  Target: Rp " . number_format($row->target, 0, ',', '.') . "\n";
    echo "  Paid:   Rp " . number_format($row->paid, 0, ',', '.') . "\n";
    echo "    Cash:     Rp " . number_format($row->paid_cash, 0, ',', '.') . "\n";
    echo "    Balance:  Rp " . number_format($row->paid_balance, 0, ',', '.') . "\n";
    echo "    Transfer: Rp " . number_format($row->paid_transfer, 0, ',', '.') . "\n";
}

foreach ($q as $row) {
    $totalTarget += $row->target;
    $totalPaid += $row->paid;
    $totalCash += $row->paid_cash;
    $totalBalance += $row->paid_balance;
    $totalTransfer += $row->paid_transfer;
}

echo "\n--- TOTALS ---\n";
echo "Total Target: Rp " . number_format($totalTarget, 0, ',', '.') . "\n";
echo "Total Paid:   Rp " . number_format($totalPaid, 0, ',', '.') . "\n";
echo "  Sum of sources: Rp " . number_format($totalCash + $totalBalance + $totalTransfer, 0, ',', '.') . "\n";
echo "  Cash:     Rp " . number_format($totalCash, 0, ',', '.') . "\n";
echo "  Balance:  Rp " . number_format($totalBalance, 0, ',', '.') . "\n";
echo "  Transfer: Rp " . number_format($totalTransfer, 0, ',', '.') . "\n";


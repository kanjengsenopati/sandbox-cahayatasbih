<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bill;
use Illuminate\Support\Facades\DB;

$ghostsWithTransactions = DB::table('bills')
    ->join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
    ->join('transaction_details', 'bills.id', '=', 'transaction_details.bill_id')
    ->whereNotNull('bill_types.deleted_at')
    ->whereNull('bills.deleted_at')
    ->select('bill_types.name', DB::raw('COUNT(DISTINCT bills.id) as total_bills'))
    ->groupBy('bill_types.name')
    ->get();

echo "### GHOST BILLS WITH TRANSACTIONS\n";
foreach ($ghostsWithTransactions as $row) {
    echo "- **{$row->name}**: {$row->total_bills} bills have transactions.\n";
}

$paidGhosts = DB::table('bills')
    ->join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
    ->whereNotNull('bill_types.deleted_at')
    ->whereNull('bills.deleted_at')
    ->where('bills.status', 'PAID')
    ->count();

echo "\nTotal PAID Ghost Bills: $paidGhosts\n";

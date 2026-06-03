<?php
require 'F:/Antigravity/Projects/cahayatasbih/vendor/autoload.php';
$app = require_once 'F:/Antigravity/Projects/cahayatasbih/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bill;
use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Models\AcademicYear;

echo "1. Academic Years:\n";
foreach (AcademicYear::all() as $ay) {
    echo "- ID: {$ay->id}, Name: {$ay->name}\n";
}

echo "\n2. Bills Summary:\n";
$billsCount = Bill::count();
$paidBillsCount = Bill::where('status', 'PAID')->count();
echo "Total Bills: $billsCount\n";
echo "Paid Bills (PAID): $paidBillsCount\n";

$billsByAY = Bill::select('academic_year_id', \DB::raw('count(*) as count'), \DB::raw('sum(amount) as total'))
    ->groupBy('academic_year_id')
    ->get();
echo "Bills by Academic Year:\n";
foreach ($billsByAY as $b) {
    $ayName = AcademicYear::find($b->academic_year_id)?->name ?? 'Unknown';
    echo "- AY: $ayName (ID: {$b->academic_year_id}), Count: {$b->count}, Total: {$b->total}\n";
}

echo "\n3. Transactions Summary:\n";
$txsCount = Transaction::count();
echo "Total Transactions: $txsCount\n";

$txsByStatus = Transaction::select('status', \DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();
echo "Transactions by Status:\n";
foreach ($txsByStatus as $t) {
    echo "- Status: {$t->status}, Count: {$t->count}\n";
}

$txsByType = Transaction::select('type', \DB::raw('count(*) as count'))
    ->groupBy('type')
    ->get();
echo "Transactions by Type:\n";
foreach ($txsByType as $t) {
    echo "- Type: {$t->type}, Count: {$t->count}\n";
}

echo "\n4. Payment Methods:\n";
foreach (PaymentMethod::all() as $pm) {
    echo "- ID: {$pm->id}, Name: {$pm->name}, Type: {$pm->type}\n";
}

echo "\n5. Detailed Paid Transactions:\n";
$paidTxs = Transaction::where('status', 'PAID')
    ->with('paymentMethod')
    ->limit(10)
    ->get();
echo "Sample Paid Transactions Count: " . $paidTxs->count() . "\n";
foreach ($paidTxs as $tx) {
    echo "- ID: {$tx->id}, Code: {$tx->code}, Type: {$tx->type}, Method: " . ($tx->paymentMethod?->name ?? 'None') . ", Amount: {$tx->pay_amount}, Paid At: {$tx->paid_at}\n";
}

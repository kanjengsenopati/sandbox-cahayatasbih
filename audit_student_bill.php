<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// NIS: 332126211 = AHMAD CHUSAENI
$student = DB::table('students')->where('nis', '332126211')->first();
if (!$student) { echo "Student not found!\n"; exit; }

echo "=== STUDENT INFO ===\n";
echo "ID: {$student->id}\n";
echo "NIS: {$student->nis}\n";
echo "Name: {$student->name}\n\n";

echo "=== RAW BILLS (PENDAFTARAN) ===\n";
$bills = DB::table('bills')
    ->join('bill_types', 'bill_types.id', '=', 'bills.bill_type_id')
    ->where('bills.student_id', $student->id)
    ->where('bill_types.name', 'like', '%PENDAFTARAN%')
    ->whereNull('bills.deleted_at')
    ->select('bills.*', 'bill_types.name as bill_type_name')
    ->get();

foreach ($bills as $b) {
    echo "Bill ID: {$b->id}\n";
    echo "  Type: {$b->bill_type_name}\n";
    echo "  Month: {$b->month} | Year: {$b->year}\n";
    echo "  Amount (Tagihan): {$b->amount}\n";
    echo "  Paid Amount (Terbayar DB): {$b->paid_amount}\n";
    echo "  Status: {$b->status}\n";
    echo "  Remaining (amount - paid_amount): " . ($b->amount - $b->paid_amount) . "\n\n";

    // Check actual transactions for this bill
    echo "  === TRANSACTION DETAILS for this bill ===\n";
    $tds = DB::table('transaction_details')
        ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
        ->where('transaction_details.bill_id', $b->id)
        ->whereNull('transaction_details.deleted_at')
        ->whereNull('transactions.deleted_at')
        ->select('transaction_details.*', 'transactions.status as tx_status', 'transactions.pay_amount as tx_pay_amount', 'transactions.paid_at', 'transactions.admin_id')
        ->get();

    $actualPaidTotal = 0;
    foreach ($tds as $td) {
        echo "  TD ID: {$td->id} | TX Status: {$td->tx_status} | TD Amount: {$td->amount} | TX Pay Amount: {$td->tx_pay_amount} | Paid At: {$td->paid_at}\n";
        if ($td->tx_status === 'PAID') {
            $actualPaidTotal += $td->amount;
        }
    }
    echo "  ACTUAL PAID (SUM from PAID transactions): {$actualPaidTotal}\n";
    echo "  MISMATCH: " . ($b->paid_amount != $actualPaidTotal ? "YES (DB: {$b->paid_amount} vs Actual: {$actualPaidTotal})" : "NO") . "\n\n";
}

echo "=== ELOQUENT ACCESSOR TEST ===\n";
$billModel = \App\Models\Bill::where('student_id', $student->id)
    ->whereHas('billType', fn($q) => $q->where('name', 'like', '%PENDAFTARAN%'))
    ->first();

if ($billModel) {
    echo "Raw DB paid_amount: " . $billModel->getAttributes()['paid_amount'] . "\n";
    echo "Accessor paid_amount: " . $billModel->paid_amount . "\n";
    echo "Status: " . $billModel->status . "\n";
    echo "Amount: " . $billModel->amount . "\n";
}

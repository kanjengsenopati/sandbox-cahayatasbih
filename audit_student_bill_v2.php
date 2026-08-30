<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = \App\Models\Student::where("nis", "332126211")->first();
echo "Student: " . $student->name . "\n";

$bills = \App\Models\Bill::where("student_id", $student->id)
    ->whereHas("billType", fn($q) => $q->where("name", "like", "%PENDAFTARAN%"))
    ->get();

echo "=== BILLS ===\n";
foreach ($bills as $b) {
    echo "Bill ID: " . $b->id . " | Amount: " . $b->amount . " | Paid DB: " . $b->getRawOriginal("paid_amount") . " | Status: " . $b->status . "\n";
    
    echo "  -- All Transaction Details (including deleted/failed) --\n";
    $tds = \App\Models\TransactionDetail::where("bill_id", $b->id)->withTrashed()->get();
    
    $sumPaid = 0;
    foreach ($tds as $td) {
        $t = \App\Models\Transaction::withTrashed()->find($td->transaction_id);
        $deletedStr = $td->trashed() ? "[DELETED]" : "";
        $tDeletedStr = $t && $t->trashed() ? "[TX_DELETED]" : "";
        $tStatus = $t ? $t->status : "N/A";
        
        echo "  TD: " . $td->amount . " | TX Status: " . $tStatus . " " . $deletedStr . " " . $tDeletedStr . "\n";
        
        if (!$td->trashed() && $t && !$t->trashed() && $t->status == "PAID") {
            $sumPaid += $td->amount;
        }
    }
    echo "  SUM of Valid PAID Tds: " . $sumPaid . "\n";
}


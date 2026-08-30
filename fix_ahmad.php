<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = \App\Models\Student::where("nis", "332126211")->first();
$bills = \App\Models\Bill::where("student_id", $student->id)->whereHas("billType", fn($q) => $q->where("name", "like", "%PENDAFTARAN%"))->get();

foreach ($bills as $b) {
    echo "Before: paid_amount = " . $b->getRawOriginal("paid_amount") . ", status = " . $b->status . "\n";
    
    $actualPaid = \App\Models\TransactionDetail::where("bill_id", $b->id)
        ->whereHas("transaction", fn($q) => $q->where("status", "PAID"))
        ->sum("amount");
        
    $b->paid_amount = $actualPaid;
    $b->status = $actualPaid >= $b->amount ? "PAID" : ($actualPaid > 0 ? "PARTIAL" : "UNPAID");
    $b->save();
    
    echo "After: paid_amount = " . $b->getRawOriginal("paid_amount") . ", status = " . $b->status . "\n";
}


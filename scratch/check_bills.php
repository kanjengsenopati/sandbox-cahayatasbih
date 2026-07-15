<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$billTypes = \App\Models\BillType::where('name', 'like', '%REGISTRASI%')->get();
if ($billTypes->count() > 0) {
    foreach ($billTypes as $bt) {
        echo "BillType: " . $bt->name . " (Type: " . $bt->type . ")\n";
        $count = \App\Models\Bill::where('bill_type_id', $bt->id)->count();
        echo "- Total bills: $count\n";
        $sampleBill = \App\Models\Bill::where('bill_type_id', $bt->id)->first();
        if ($sampleBill) {
            echo "- Sample amount: " . $sampleBill->amount . "\n";
            $student = \App\Models\Student::find($sampleBill->student_id);
            echo "- Sample student: " . ($student ? $student->name . " (" . $student->nis . ")" : "N/A") . "\n";
        }
    }
} else {
    echo "No REGISTRASI bill type found.\n";
}

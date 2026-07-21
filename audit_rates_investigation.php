<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AcademicYear;
use App\Models\BillType;
use App\Models\PaymentRate;
use App\Models\PaymentRateItem;
use App\Models\Bill;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;

$out = "";
$out .= "=== 1. ACADEMIC YEARS ===\n";
$academicYears = AcademicYear::all();
foreach ($academicYears as $ay) {
    $out .= "ID: {$ay->id} | Name: {$ay->name}\n";
}

$out .= "\n=== 2. BILL TYPES ===\n";
$billTypes = BillType::with('academicYear')->get();
foreach ($billTypes as $bt) {
    $out .= "ID: {$bt->id} | Name: {$bt->name} | Type: {$bt->type} | AY: " . ($bt->academicYear->name ?? 'None') . "\n";
}

$out .= "\n=== 3. PAYMENT RATES & ITEMS ===\n";
$rates = PaymentRate::with(['billType', 'paymentRateItems', 'paymentRateClassrooms', 'paymentRateStudents'])->get();
foreach ($rates as $rate) {
    $out .= "Rate ID: {$rate->id} | BillType: " . ($rate->billType->name ?? 'N/A') . " (AY: " . ($rate->billType->academicYear->name ?? 'N/A') . ") | Amount: {$rate->amount} | Type: {$rate->type}\n";
    $classCount = $rate->paymentRateClassrooms->count();
    $studentCount = $rate->paymentRateStudents->count();
    $out .= "   Assigned Classrooms: {$classCount} | Assigned Students: {$studentCount}\n";
    foreach ($rate->paymentRateItems as $item) {
        $out .= "   -> Item Month: {$item->month} | Year: {$item->year} | Amount: {$item->amount}\n";
    }
}

$out .= "\n=== 4. STUDENT COUNT & SUMMARY ===\n";
$totalStudents = Student::count();
$out .= "Total Students: {$totalStudents}\n";

$out .= "\n=== 5. AUDIT BILLS PER TYPE ===\n";

$syahriahTypes = BillType::where('name', 'like', '%SYAHRIAH%')->orWhere('name', 'like', '%SYAHRIYAH%')->get();
$aplikasiTypes = BillType::where('name', 'like', '%APLIKASI%')->get();
$zarkasiTypes = BillType::where('name', 'like', '%ZARKASI%')->orWhere('name', 'like', '%ZARKASYI%')->get();

$out .= "Syahriah Types:\n";
foreach ($syahriahTypes as $st) {
    $billCount = Bill::where('bill_type_id', $st->id)->count();
    $out .= "  - ID: {$st->id} | Name: {$st->name} | Bills Count: {$billCount}\n";
}

$out .= "Aplikasi Types:\n";
foreach ($aplikasiTypes as $at) {
    $billCount = Bill::where('bill_type_id', $at->id)->count();
    $out .= "  - ID: {$at->id} | Name: {$at->name} | Bills Count: {$billCount}\n";
}

$out .= "Zarkasi Types:\n";
foreach ($zarkasiTypes as $zt) {
    $billCount = Bill::where('bill_type_id', $zt->id)->count();
    $out .= "  - ID: {$zt->id} | Name: {$zt->name} | Bills Count: {$billCount}\n";
}

file_put_contents(__DIR__.'/audit_output.txt', $out);
echo "DONE writing to audit_output.txt\n";

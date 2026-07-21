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
use Illuminate\Support\Facades\DB;

$out = "=== ACADEMIC YEARS ===\n";
$ays = AcademicYear::select('id', 'name', 'is_active')->get();
foreach ($ays as $ay) {
    $out .= "ID: {$ay->id} | Name: {$ay->name} | Active: {$ay->is_active}\n";
}

$out .= "\n=== TARGET BILL TYPES (SYAHRIAH, APLIKASI, ZARKASI) ===\n";
$bts = BillType::select('id', 'name', 'academic_year_id', 'type')
    ->where(function($q) {
        $q->where('name', 'like', '%SYAHR%')
          ->orWhere('name', 'like', '%APLIKASI%')
          ->orWhere('name', 'like', '%ZARKAS%');
    })->get();

foreach ($bts as $bt) {
    $ayName = $ays->firstWhere('id', $bt->academic_year_id)->name ?? 'N/A';
    $out .= "BillType ID: {$bt->id} | Name: {$bt->name} | AY: {$ayName} | Type: {$bt->type}\n";
    
    // Check Rates for this BillType
    $rates = PaymentRate::where('bill_type_id', $bt->id)->get();
    foreach ($rates as $r) {
        $out .= "  -> PaymentRate ID: {$r->id} | Amount: {$r->amount} | Type: {$r->type}\n";
        
        $items = PaymentRateItem::where('payment_rate_id', $r->id)->get();
        foreach ($items as $item) {
            $out .= "     * Item Month: {$item->month} | Year: {$item->year} | Amount: {$item->amount}\n";
        }
    }
}

file_put_contents(__DIR__.'/targeted_output.txt', $out);
echo "DONE targeted output\n";

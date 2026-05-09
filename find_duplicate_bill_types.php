<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\BillType;
use Illuminate\Support\Facades\DB;

$duplicates = DB::table('bill_types')
    ->whereNull('deleted_at')
    ->select('name', 'academic_year_id', DB::raw('COUNT(*) as count'))
    ->groupBy('name', 'academic_year_id')
    ->havingRaw('COUNT(*) > 1')
    ->get();

echo "Found " . $duplicates->count() . " duplicate BillType name groups.\n";

foreach ($duplicates as $dup) {
    $types = BillType::where('name', $dup->name)
        ->where('academic_year_id', $dup->academic_year_id)
        ->get();
    
    echo "Group: '{$dup->name}' | AY: {$dup->academic_year_id}\n";
    foreach ($types as $t) {
        $billCount = $t->bills()->count();
        echo "  - ID: {$t->id} | Created: {$t->created_at} | Bills: {$billCount}\n";
    }
}

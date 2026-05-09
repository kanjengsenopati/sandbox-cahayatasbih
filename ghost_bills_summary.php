<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bill;
use Illuminate\Support\Facades\DB;

$ghostSummary = DB::table('bills')
    ->join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
    ->whereNotNull('bill_types.deleted_at') // Induk dihapus
    ->whereNull('bills.deleted_at')       // Anak masih aktif
    ->select('bill_types.name', 'bill_types.deleted_at as deleted_time', DB::raw('COUNT(*) as total_ghost_bills'))
    ->groupBy('bill_types.name', 'bill_types.deleted_at')
    ->orderBy('total_ghost_bills', 'desc')
    ->get();

echo "### ANALISIS TAGIHAN HANTU (GHOST BILLS)\n";
echo "Ditemukan tagihan aktif yang Jenis Bayarnya (BillType) sudah dihapus:\n\n";

if ($ghostSummary->isEmpty()) {
    echo "Tidak ditemukan tagihan hantu.\n";
} else {
    foreach ($ghostSummary as $row) {
        echo "- **{$row->name}**: {$row->total_ghost_bills} tagihan (Jenis Bayar dihapus pada: {$row->deleted_time})\n";
    }
}

echo "\nTotal Keseluruhan: " . $ghostSummary->sum('total_ghost_bills') . " tagihan.\n";

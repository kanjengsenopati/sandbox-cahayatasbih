<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bill;
use App\Models\BillType;

$ghosts = Bill::whereHas('billType', function($q) {
    $q->onlyTrashed();
})->with(['billType' => fn($q) => $q->withTrashed()])->limit(10)->get();

echo "### GHOST BILLS TIMESTAMPS\n";
foreach ($ghosts as $bill) {
    echo "Bill ID: {$bill->id} | Type: {$bill->billType->name} | TypeDeletedAt: {$bill->billType->deleted_at} | BillUpdatedAt: {$bill->updated_at}\n";
}

$typesCountByDeletedAt = BillType::onlyTrashed()
    ->select('deleted_at', \DB::raw('count(*) as count'))
    ->groupBy('deleted_at')
    ->get();

echo "\n### BILL TYPES DELETION BATCHES\n";
foreach ($typesCountByDeletedAt as $row) {
    echo "Deleted At: {$row->deleted_at} | Count: {$row->count}\n";
}

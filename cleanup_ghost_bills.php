<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Find all soft-deleted BillTypes
$deletedTypeIds = DB::table('bill_types')->whereNotNull('deleted_at')->pluck('id');

// Soft-delete all bills that belong to those BillTypes
$affectedRows = DB::table('bills')
    ->whereIn('bill_type_id', $deletedTypeIds)
    ->whereNull('deleted_at')
    ->update(['deleted_at' => now()]);

echo "Successfully soft-deleted $affectedRows ghost bills.\n";

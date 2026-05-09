<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$types = \App\Models\BillType::whereIn('id', ['6fc63a93-92c0-4a61-9071-778facbcc64b', 'dd65e838-fecf-48f5-b986-5c94e21e0d93'])->get();

foreach ($types as $type) {
    echo "ID: {$type->id} | Name: {$type->name} | AY: {$type->academic_year_id} | Type: {$type->type} | Created: {$type->created_at}\n";
}

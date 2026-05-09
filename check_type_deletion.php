<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$type1 = \App\Models\BillType::withTrashed()->find('6fc63a93-92c0-4a61-9071-778facbcc64b');
$type2 = \App\Models\BillType::withTrashed()->find('dd65e838-fecf-48f5-b986-5c94e21e0d93');

echo "Type 1: " . ($type1 ? "ID: {$type1->id} | Name: {$type1->name} | Deleted: " . ($type1->deleted_at ?: 'Active') : "Not Found") . "\n";
echo "Type 2: " . ($type2 ? "ID: {$type2->id} | Name: {$type2->name} | Deleted: " . ($type2->deleted_at ?: 'Active') : "Not Found") . "\n";

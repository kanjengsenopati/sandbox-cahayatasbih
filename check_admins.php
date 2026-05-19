<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ALL ADMINS ===\n";
$admins = \App\Models\Admin::all();
foreach ($admins as $a) {
    echo "ID: {$a->id}, Name: {$a->name}, Phone: '{$a->phone}', Email: {$a->email}, Scope: {$a->access_scope}\n";
}

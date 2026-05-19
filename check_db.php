<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== WALISANTRI (users) ===\n";
$users = \App\Models\User::where('phone', '08123456789')->orWhere('name', 'like', '%Siswanto%')->get();
foreach ($users as $u) {
    echo "ID: {$u->id}, Name: {$u->name}, Phone: {$u->phone}, Active: {$u->is_active}\n";
}

echo "\n=== ASATIDZ/ADMINS (admins) ===\n";
$admins = \App\Models\Admin::where('phone', '08123456789')->orWhere('name', 'like', '%Siswanto%')->get();
foreach ($admins as $a) {
    echo "ID: {$a->id}, Name: {$a->name}, Phone: {$a->phone}, Active: {$a->is_active}, Scope: {$a->access_scope}\n";
}

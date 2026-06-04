<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;

$admins = Admin::with('roles')->take(5)->get();
foreach ($admins as $admin) {
    echo "ID: " . $admin->id . "\n";
    echo "Name: " . $admin->name . "\n";
    echo "Email: " . $admin->email . "\n";
    echo "Roles: " . implode(', ', $admin->roles->pluck('name')->toArray()) . "\n";
    echo "Outlet IDs: " . implode(', ', $admin->getOutletIds()) . "\n";
    echo "---------------------------------\n";
}

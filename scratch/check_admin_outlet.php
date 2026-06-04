<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;

$admin = Admin::where('name', 'like', '%FUAD KHOIRUDDIN%')->first();
if ($admin) {
    echo "Admin Name: " . $admin->name . "\n";
    echo "outlet_id (column): " . var_export($admin->outlet_id, true) . "\n";
    echo "adminOutlet count: " . $admin->adminOutlet->count() . "\n";
    foreach ($admin->adminOutlet as $ao) {
        echo "  - Assigned Outlet ID: " . $ao->outlet_id . " | Name: " . ($ao->outlet?->name ?? 'N/A') . "\n";
    }
} else {
    echo "Admin not found!\n";
}

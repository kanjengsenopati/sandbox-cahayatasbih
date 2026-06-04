<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;

$names = ['FUAD KHOIRUDDIN', 'AHMAD DHANI'];
foreach ($names as $name) {
    $admin = Admin::where('name', 'LIKE', '%' . $name . '%')->first();
    if ($admin) {
        echo "Admin: {$admin->name}, ID: {$admin->id}, outlet_id: " . ($admin->outlet_id ?? 'NULL') . "\n";
        echo "Assigned outlet IDs: " . implode(', ', $admin->getOutletIds()) . "\n";
    } else {
        echo "Admin: $name not found!\n";
    }
}

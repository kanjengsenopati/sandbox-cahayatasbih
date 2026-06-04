<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Outlet;

$outlets = Outlet::all();
echo "Total outlets: " . $outlets->count() . "\n";
foreach ($outlets as $o) {
    echo "ID: {$o->id}, Name: {$o->name}, Code: {$o->code}\n";
}

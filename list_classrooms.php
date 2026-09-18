<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$classrooms = App\Models\Classroom::orderBy('name')->get();
foreach ($classrooms as $c) {
    echo $c->name . "\n";
}

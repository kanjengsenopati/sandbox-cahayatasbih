<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());
echo json_encode(\App\Models\Information::latest()->take(3)->get(['id', 'title', 'image'])->toArray(), JSON_PRETTY_PRINT);

<?php
require 'F:/Antigravity/Projects/cahayatasbih/vendor/autoload.php';
$app = require_once 'F:/Antigravity/Projects/cahayatasbih/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo implode(', ', Illuminate\Support\Facades\Schema::getColumnListing('transaction_details'));
echo "\n";

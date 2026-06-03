<?php
require 'F:/Antigravity/Projects/cahayatasbih/vendor/autoload.php';
$app = require_once 'F:/Antigravity/Projects/cahayatasbih/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$txId = '0c0f40a8-45c5-4170-8441-70b5eda24054';
echo "=== TRANSACTION DETAILS FOR $txId ===\n\n";

$tx = DB::selectOne("SELECT * FROM transactions WHERE id = ?", [$txId]);
print_r($tx);

echo "\nDetails:\n";
$details = DB::select("SELECT * FROM transaction_details WHERE transaction_id = ?", [$txId]);
print_r($details);

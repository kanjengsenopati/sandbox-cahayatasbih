<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tx = \App\Models\Transaction::where('transaction_code', 'CHT-20260912002')->first();
echo "Transaction: \n";
echo json_encode($tx);
$details = \App\Models\TransactionDetail::where('transaction_id', $tx->id)->get();
echo "\n\nDetails: \n";
echo json_encode($details);

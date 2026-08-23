<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rates = App\Models\PaymentRate::all();
foreach($rates as $r) {
    echo "ID: {$r->id} | Name: {$r->name} | Type: {$r->type} | BillType: {$r->bill_type_id}\n";
    $items = $r->paymentRateItems;
    foreach($items as $i) {
        if ($i->month == 8) {
            echo "   Month: 8, Year: {$i->year}, Amount: {$i->amount}, PRI_ID: {$i->id}\n";
        }
    }
}

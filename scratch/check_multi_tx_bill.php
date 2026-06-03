<?php
require 'F:/Antigravity/Projects/cahayatasbih/vendor/autoload.php';
$app = require_once 'F:/Antigravity/Projects/cahayatasbih/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== INSPECTING MULTI-TX BILL (EFFICIENT) ===\n\n";

$sample = DB::selectOne("
    SELECT td.bill_id, COUNT(t.id) as tx_count
    FROM transaction_details td
    JOIN transactions t ON td.transaction_id = t.id
    WHERE t.status = 'PAID' AND t.type = 'BILL'
    GROUP BY td.bill_id
    HAVING tx_count > 1
    LIMIT 1
");

if ($sample) {
    $billId = $sample->bill_id;
    echo "Sample Bill ID: $billId (Paid Tx Count: {$sample->tx_count})\n";
    $bill = DB::selectOne("SELECT * FROM bills WHERE id = ?", [$billId]);
    print_r($bill);
    
    echo "\nTransaction Details:\n";
    $details = DB::select("
        SELECT td.id, td.transaction_id, t.status, t.pay_amount, pm.name as pm_name, pm.type as pm_type
        FROM transaction_details td
        JOIN transactions t ON td.transaction_id = t.id
        JOIN payment_methods pm ON t.payment_method_id = pm.id
        WHERE td.bill_id = ?
    ", [$billId]);
    print_r($details);
} else {
    echo "No bills with multiple PAID transactions found.\n";
}

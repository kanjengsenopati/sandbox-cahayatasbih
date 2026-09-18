<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Find: for anomalies that currently show parent names, what admin actually approved them in Arsip Riwayat?
// Check if there's a payment_method or other differentiator
echo "=== CHECK PAYMENT METHODS ===\n";
$methods = DB::select("SELECT id, name FROM payment_methods LIMIT 20");
print_r($methods);

// For the Sri Lestari case: is there another transaction for the same student+bill with a real admin?
echo "\n=== CHECK TRANSACTION DETAILS FOR SRI LESTARI ADMIN_ID ===\n";
$txDetails = DB::select("
    SELECT t.id, t.admin_id, t.user_id, t.payment_method_id, pm.name as payment_method,
           t.pay_amount, t.status, t.paid_at, t.xendit_id, t.payment_link
    FROM transactions t
    LEFT JOIN payment_methods pm ON pm.id = t.payment_method_id
    WHERE t.admin_id = 'c2e7d4ab-303b-4bad-8a0f-6cc1a28c1d05' AND t.status = 'PAID'
    LIMIT 5
");
print_r($txDetails);

// Check: Is admin_id sometimes a user UUID? Let's count
echo "\n=== ADMIN_ID RESOLUTION STATS ===\n";
$stats = DB::select("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN a.id IS NOT NULL THEN 1 ELSE 0 END) as in_admins,
        SUM(CASE WHEN u.id IS NOT NULL AND a.id IS NULL THEN 1 ELSE 0 END) as only_in_users,
        SUM(CASE WHEN a.id IS NULL AND u.id IS NULL THEN 1 ELSE 0 END) as in_neither
    FROM transactions t
    LEFT JOIN admins a ON a.id = t.admin_id
    LEFT JOIN users u ON u.id = t.admin_id
    WHERE t.admin_id IS NOT NULL AND t.status = 'PAID'
");
print_r($stats);

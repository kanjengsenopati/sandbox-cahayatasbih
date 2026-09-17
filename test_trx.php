<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$q = DB::connection()->select("
    SELECT t.id, t.user_id, t.admin_id, u.name as user_name, a.name as admin_name
    FROM transactions t
    JOIN students s ON s.id = t.student_id
    LEFT JOIN users u ON u.id = t.user_id
    LEFT JOIN admins a ON a.id = t.admin_id
    WHERE s.name LIKE '%VIRZINIA%' AND t.pay_amount = 10000
    LIMIT 10
");
print_r($q);

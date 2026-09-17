<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 1. Check import_logs table structure
echo "=== IMPORT_LOGS COLUMNS ===\n";
$cols = Illuminate\Support\Facades\Schema::getColumnListing('import_logs');
print_r($cols);

// 2. Get a sample import_log record
echo "\n=== SAMPLE IMPORT_LOG ===\n";
$sample = DB::connection()->select("SELECT * FROM import_logs LIMIT 3");
print_r($sample);

// 3. Check Sri Lestari case - find transactions where admin_id points to users table
echo "\n=== SRI LESTARI INVESTIGATION ===\n";
$sri = DB::connection()->select("
    SELECT t.id, t.admin_id, t.user_id, t.import_log_id, t.pay_amount, t.status,
           a.name as admin_name, 
           u_admin.name as user_on_admin_id,
           u_parent.name as user_on_user_id
    FROM transactions t
    LEFT JOIN admins a ON a.id = t.admin_id
    LEFT JOIN users u_admin ON u_admin.id = t.admin_id
    LEFT JOIN users u_parent ON u_parent.id = t.user_id
    WHERE u_admin.name LIKE '%Sri Lestari%' OR u_parent.name LIKE '%Sri Lestari%'
    LIMIT 5
");
print_r($sri);

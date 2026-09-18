<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check: does Sri Lestari's admin_id (c2e7d4ab...) exist in admins table?
echo "=== SRI LESTARI ADMIN_ID CHECK ===\n";
$adminId = 'c2e7d4ab-303b-4bad-8a0f-6cc1a28c1d05';
$inAdmins = DB::select("SELECT id, name FROM admins WHERE id = ?", [$adminId]);
$inUsers = DB::select("SELECT id, name FROM users WHERE id = ?", [$adminId]);
echo "In admins table: "; print_r($inAdmins);
echo "In users table: "; print_r($inUsers);

// Check: what PAID transactions have this admin_id with anomaly (UNPAID bills)?
echo "\n=== PAID TRANSACTIONS WITH SRI LESTARI ADMIN_ID ===\n";
$txs = DB::select("
    SELECT t.id, t.admin_id, t.user_id, t.pay_amount, t.status, t.paid_at,
           s.name as student_name
    FROM transactions t
    JOIN students s ON s.id = t.student_id
    WHERE t.admin_id = ? AND t.status = 'PAID'
    LIMIT 3
", [$adminId]);
print_r($txs);

// Check import_logs admin lookup: does import_logs.admin_id point to admins table?
echo "\n=== IMPORT_LOGS ADMIN LOOKUP ===\n";
$importAdmins = DB::select("
    SELECT il.id, il.admin_id, a.name as admin_name, u.name as user_name
    FROM import_logs il
    LEFT JOIN admins a ON a.id = il.admin_id
    LEFT JOIN users u ON u.id = il.admin_id
    LIMIT 5
");
print_r($importAdmins);

// Check: for Sri Lestari anomaly case, is there a user_id on the transaction?
echo "\n=== SRI LESTARI PAID TRANSACTIONS WITH USER_ID ===\n";
$txsWithUser = DB::select("
    SELECT t.id, t.admin_id, t.user_id, t.pay_amount, t.status, t.paid_at,
           a.name as admin_name, u_on_admin.name as user_on_adminid, u_on_user.name as user_on_userid,
           s.name as student_name
    FROM transactions t
    JOIN students s ON s.id = t.student_id
    JOIN transaction_details td ON td.transaction_id = t.id
    JOIN bills b ON b.id = td.bill_id
    LEFT JOIN admins a ON a.id = t.admin_id
    LEFT JOIN users u_on_admin ON u_on_admin.id = t.admin_id
    LEFT JOIN users u_on_user ON u_on_user.id = t.user_id
    WHERE t.status = 'PAID' AND b.status = 'UNPAID' AND b.deleted_at IS NULL AND td.deleted_at IS NULL AND t.deleted_at IS NULL
    AND u_on_admin.name IS NOT NULL AND a.name IS NULL
    LIMIT 5
");
print_r($txsWithUser);

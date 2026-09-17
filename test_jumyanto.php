<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$a = DB::connection()->select("SELECT * FROM admins WHERE name LIKE '%JUMYANTO%'");
$u = DB::connection()->select("SELECT * FROM users WHERE name LIKE '%JUMYANTO%'");
echo "Admins: \n"; print_r($a);
echo "Users: \n"; print_r($u);

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$a = DB::connection()->select("SELECT * FROM admins WHERE id='c3083bb4-570d-4b1b-a1f8-3503d230b64d'");
$u = DB::connection()->select("SELECT * FROM users WHERE id='c3083bb4-570d-4b1b-a1f8-3503d230b64d'");
echo "Admins: \n"; print_r($a);
echo "Users: \n"; print_r($u);

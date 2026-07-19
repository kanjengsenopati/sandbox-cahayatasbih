<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$templates = \App\Models\NotificationTemplate::all();
foreach ($templates as $t) {
    echo "Key: {$t->key}\nTitle: {$t->title_template}\nBody: {$t->body_template}\n\n";
}

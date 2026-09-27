<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\Gate::before(fn() => true);
\Illuminate\Support\Facades\Auth::shouldUse('web');
\Illuminate\Support\Facades\Auth::login(\App\Models\User::first());

$controller = app()->make(\App\Http\Controllers\Admin\BillController::class);

// 1. Transfer tab
$request1 = \Illuminate\Http\Request::create('/admin/bill', 'GET', ['tab' => 'transfer'], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
app()->instance('request', $request1);
$t0 = microtime(true);
$controller->index()->getContent();
$t1 = microtime(true);
echo "1. AJAX Transfer table: " . round($t1 - $t0, 4) . "s\n";

// 2. History tab
$request2 = \Illuminate\Http\Request::create('/admin/bill', 'GET', ['tab' => 'history'], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
app()->instance('request', $request2);
$t2 = microtime(true);
$controller->index()->getContent();
$t3 = microtime(true);
echo "2. AJAX History table: " . round($t3 - $t2, 4) . "s\n";

// 3. Archive tab
$request3 = \Illuminate\Http\Request::create('/admin/bill', 'GET', ['tab' => 'archive'], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
app()->instance('request', $request3);
$t4 = microtime(true);
$controller->index()->getContent();
$t5 = microtime(true);
echo "3. AJAX Archive table: " . round($t5 - $t4, 4) . "s\n";

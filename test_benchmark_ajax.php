<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\Gate::before(fn() => true);
\Illuminate\Support\Facades\Auth::shouldUse('web');
\Illuminate\Support\Facades\Auth::login(\App\Models\User::first());

$controller = app()->make(\App\Http\Controllers\Admin\BillController::class);
$request = \Illuminate\Http\Request::create('/admin/bill', 'GET', [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
app()->instance('request', $request);

$t0 = microtime(true);
$response = $controller->index();
$t1 = microtime(true);
echo "1. getTransactionData: " . round($t1 - $t0, 4) . "s\n";

if ($response instanceof \Illuminate\Http\JsonResponse) {
    // If it's a datatable response, we might need to get the content to trigger the actual DB query
    $t2 = microtime(true);
    $content = $response->getContent();
    $t3 = microtime(true);
    echo "2. JSON serialization (executes query): " . round($t3 - $t2, 4) . "s\n";
}

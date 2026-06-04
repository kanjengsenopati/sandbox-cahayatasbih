<?php
// Override env vars before bootstrap
putenv('CACHE_DRIVER=file');
putenv('SESSION_DRIVER=file');
putenv('QUEUE_CONNECTION=sync');
$_ENV['CACHE_DRIVER'] = 'file';
$_ENV['SESSION_DRIVER'] = 'file';
$_ENV['QUEUE_CONNECTION'] = 'sync';

include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use App\Http\Controllers\Admin\PosTransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$admin = Admin::whereHas('roles', function($q) {
    $q->where('name', 'Super Admin');
})->first() ?: Admin::first();

Auth::login($admin);

// Outlet ID for CAHAYA MART
$cahayaMartId = '38f9e045-879a-424b-8150-d553ed846c61';

// Simulate AJAX Request for totals with date range May 1 to May 9, 2026
$request = Request::create('/pos-transaction', 'GET', [
    'data' => 'total',
    'start_date' => '2026-05-01',
    'end_date' => '2026-05-09',
    'outlet_id' => $cahayaMartId,
]);
$request->headers->set('X-Requested-With', 'XMLHttpRequest');
$app->instance('request', $request);

$controller = new PosTransactionController();
$response = $controller->index($request);

echo "Status code: " . $response->getStatusCode() . "\n";
echo "Response JSON:\n";
echo json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT) . "\n";

<?php
// Override env vars before bootstrap
putenv('CACHE_DRIVER=file');
putenv('SESSION_DRIVER=file');
putenv('QUEUE_CONNECTION=sync');
$_ENV['CACHE_DRIVER'] = 'file';
$_ENV['SESSION_DRIVER'] = 'file';
$_ENV['QUEUE_CONNECTION'] = 'sync';

include __DIR__ . '/../vendor/autoload.php';
// Manually require helper if not registered in autoload_files yet
if (file_exists(__DIR__ . '/../app/Helpers/helpers.php')) {
    require_once __DIR__ . '/../app/Helpers/helpers.php';
}

$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Admin;
use App\Http\Controllers\Admin\PosTransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Let's find a Super Admin or an admin with POS permission
$admin = Admin::whereHas('roles', function($q) {
    $q->where('name', 'Super Admin');
})->first();

if (!$admin) {
    // fallback to any admin
    $admin = Admin::first();
}

if (!$admin) {
    die("No admin user found in database!\n");
}

echo "Testing as user: " . $admin->name . " (" . $admin->email . ")\n";
Auth::login($admin);

$koperasiId = '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

// 1. Simulate AJAX Request for transactions table (Koperasi filter) with pagination
echo "\n--- SIMULATING TRANSACTIONS TABLE AJAX (KOPERASI) ---\n";
try {
    $request = Request::create('/pos-transaction', 'GET', [
        'data' => 'table',
        'start_date' => '2026-05-01',
        'end_date' => '2026-05-31',
        'status' => '',
        'outlet_id' => $koperasiId,
        'draw' => '1',
        'start' => '0',
        'length' => '10'
    ]);
    // Set AJAX headers
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    
    // Bind to Laravel application container
    $app->instance('request', $request);

    $controller = new PosTransactionController();
    $response = $controller->index($request);
    echo "Status code: " . $response->getStatusCode() . "\n";
    echo "Content snippet:\n" . substr($response->getContent(), 0, 1000) . "\n";
} catch (\Exception $e) {
    echo "EXCEPTION THROWN in table request:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// 2. Simulate AJAX Request for top items (Koperasi filter)
echo "\n--- SIMULATING TOP ITEMS AJAX (KOPERASI) ---\n";
try {
    $request = Request::create('/pos-transaction', 'GET', [
        'type' => 'top-items',
        'start_date' => '2026-05-01',
        'end_date' => '2026-05-31',
        'outlet_id' => $koperasiId,
        'draw' => '1',
        'start' => '0',
        'length' => '10'
    ]);
    // Set AJAX headers
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    
    // Bind to Laravel application container
    $app->instance('request', $request);

    $controller = new PosTransactionController();
    $response = $controller->index($request);
    echo "Status code: " . $response->getStatusCode() . "\n";
    echo "Content snippet:\n" . substr($response->getContent(), 0, 1000) . "\n";
} catch (\Exception $e) {
    echo "EXCEPTION THROWN in top-items request:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

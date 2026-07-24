<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = 'ae719bc4-c63d-4f19-bc2f-87cc09ec0099';

echo "=== TESTING SHOW WITH NON-EXISTENT ID: $id ===\n";

$user = \App\Models\User::first();
if ($user) \Auth::login($user);

$request = \Illuminate\Http\Request::create("/bill-type/$id?academic_year_id=56f6f5bf-ca0a-4230-af38-8e2f3fcf35d1", 'GET');
app()->instance('request', $request);

try {
    $controller = new \App\Http\Controllers\Admin\BillTypeController();
    $response = $controller->show($request, $id);
    if ($response instanceof \Illuminate\Http\RedirectResponse) {
        echo "[SUCCESS HANDLED] Gracefully redirected to: " . $response->getTargetUrl() . "\n";
    } else {
        echo "Response: " . get_class($response) . "\n";
    }
} catch (\Throwable $e) {
    echo "EXCEPTIONAL ERROR:\n" . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
}

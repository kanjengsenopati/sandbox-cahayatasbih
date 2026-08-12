<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = \Illuminate\Http\Request::create('/payment-rate/3f8b8ee9-76c4-477f-bfaf-9b6d9c958fd4', 'GET', ['type' => 'bill']);
$request->headers->set('X-Requested-With', 'XMLHttpRequest');
app()->instance('request', $request);

try {
    $controller = app()->make(App\Http\Controllers\Admin\PaymentRateController::class);
    $response = $controller->show('3f8b8ee9-76c4-477f-bfaf-9b6d9c958fd4');
    echo "SUCCESS\n";
    if (is_object($response) && method_exists($response, 'getContent')) {
        echo substr($response->getContent(), 0, 500);
    }
} catch (\Exception $e) {
    echo "EXCEPTION:\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . " on line " . $e->getLine() . "\n";
    echo $e->getTraceAsString();
} catch (\Error $e) {
    echo "ERROR:\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . " on line " . $e->getLine() . "\n";
}

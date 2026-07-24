<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = 'ae719bc4-c63d-4f19-bc2f-87cc09ec0099';

try {
    $bt = \App\Models\BillType::find($id);
    if (!$bt) {
        echo "BillType not found with ID $id. Searching all BillTypes...\n";
        $bt = \App\Models\BillType::first();
        if ($bt) {
            $id = $bt->id;
            echo "Using first BillType ID: $id ({$bt->name})\n";
        } else {
            echo "No BillTypes in DB!\n";
            exit;
        }
    } else {
        echo "Found BillType ID $id: {$bt->name}\n";
    }

    $request = \Illuminate\Http\Request::create("/bill-type/$id", 'GET');
    $controller = new \App\Http\Controllers\Admin\BillTypeController();
    $response = $controller->show($request, $id);
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "SUCCESS rendering show view!\n";

} catch (\Throwable $e) {
    echo "EXCEPTIONAL ERROR caught:\n";
    echo $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

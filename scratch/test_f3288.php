<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = 'f32889d7-c082-4507-8b5f-3512a4b01685';
$academicYearId = '56f6f5bf-ca0a-4230-af38-8e2f3fcf35d1';

echo "=== TESTING EXACT REQUEST FOR ID: $id ===\n";

$bt = \App\Models\BillType::find($id);
if (!$bt) {
    echo "BillType $id not found in local DB!\n";
    exit;
}
echo "BillType name: {$bt->name}, academic_year_id: {$bt->academic_year_id}\n";

$user = \App\Models\User::first();
if ($user) \Auth::login($user);

$request = \Illuminate\Http\Request::create("/bill-type/$id?academic_year_id=$academicYearId", 'GET');
app()->instance('request', $request);

try {
    $controller = new \App\Http\Controllers\Admin\BillTypeController();
    $response = $controller->show($request, $id);
    if ($response instanceof \Illuminate\View\View) {
        $content = $response->render();
        echo "View rendered successfully! Length: " . strlen($content) . " bytes\n";
    } else {
        echo "Response class: " . get_class($response) . "\n";
    }
} catch (\Throwable $e) {
    echo "EXCEPTIONAL ERROR:\n" . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::first();
if ($user) {
    \Auth::login($user);
}

$out = "=== TESTING ALL BILL TYPES SHOW METHOD WITH ACADEMIC_YEAR_ID FILTER ===\n";

$billTypes = \App\Models\BillType::all();
$out .= "Found " . $billTypes->count() . " BillTypes in DB.\n";

$academicYearId = '56f6f5bf-ca0a-4230-af38-8e2f3fcf35d1';

foreach ($billTypes as $bt) {
    try {
        $id = $bt->id;
        $request = \Illuminate\Http\Request::create("/bill-type/$id?academic_year_id=$academicYearId", 'GET');
        
        // Bind request to app
        app()->instance('request', $request);

        $controller = new \App\Http\Controllers\Admin\BillTypeController();
        $response = $controller->show($request, $id);
        
        if ($response instanceof \Illuminate\View\View) {
            $content = $response->render();
            $out .= "[OK VIEW] BillType ID: $id ({$bt->name})\n";
        } else {
            $out .= "[OK RESP] BillType ID: $id ({$bt->name}) -> " . get_class($response) . "\n";
        }
    } catch (\Throwable $e) {
        $out .= "[ERROR 500] BillType ID: {$bt->id} ({$bt->name}):\n";
        $out .= "Message: " . $e->getMessage() . "\n";
        $out .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
        $out .= "Trace snippet:\n" . implode("\n", array_slice(explode("\n", $e->getTraceAsString()), 0, 10)) . "\n\n";
    }
}

file_put_contents(__DIR__ . '/diagnose_500_res.txt', $out);
echo "Wrote output to diagnose_500_res.txt\n";

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$studentId = \App\Models\Student::where('nis', '330200377')->first()->id;

\Illuminate\Support\Facades\Gate::before(fn() => true);
\Illuminate\Support\Facades\Auth::shouldUse('web');
\Illuminate\Support\Facades\Auth::login(\App\Models\User::first());

$controller = app()->make(\App\Http\Controllers\Admin\BillController::class);
$request = \Illuminate\Http\Request::create('/admin/bill', 'GET', ['student_id' => $studentId, 'academic_year_id' => 'all']);
app()->instance('request', $request);

$t0 = microtime(true);
$response = $controller->index();
$t1 = microtime(true);
echo "1. Controller logic: " . round($t1 - $t0, 4) . "s\n";

$t2 = microtime(true);
$rendered = $response->render();
$t3 = microtime(true);
echo "2. View rendering: " . round($t3 - $t2, 4) . "s\n";

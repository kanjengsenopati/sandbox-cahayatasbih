<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = \App\Models\Student::where('nis', '330200377')->first();
if (!$student) {
    echo "Student not found\n";
    exit;
}
$studentId = $student->id;

echo "Benchmarking for Student ID: {$studentId} ({$student->name})\n";

$start = microtime(true);
$preloadedRates = \App\Services\TransactionService::getCachedPreloadedRates();
$t1 = microtime(true);
echo "1. getCachedPreloadedRates: " . round($t1 - $start, 4) . "s\n";

$request = \Illuminate\Http\Request::create('/admin/bill', 'GET', ['student_id' => $studentId, 'academic_year_id' => 'all']);
app()->instance('request', $request);

// Bypass authorization temporarily for benchmarking
\Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
    return true;
});

// Mock Auth with a user that has web guard
$user = \App\Models\User::whereHas('roles', function($q) { $q->where('name', 'Super Admin'); })->first();
if (!$user) $user = \App\Models\User::first();
\Illuminate\Support\Facades\Auth::shouldUse('web');
\Illuminate\Support\Facades\Auth::login($user);

$controller = app()->make(\App\Http\Controllers\Admin\BillController::class);

$t2 = microtime(true);
$response = $controller->index();
$t3 = microtime(true);

echo "2. BillController@index total: " . round($t3 - $t2, 4) . "s\n";

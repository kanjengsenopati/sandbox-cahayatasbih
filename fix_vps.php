<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Force default DB to mysql (which uses the DB_MASTER variables based on .env maybe?)
// Let's just create a PDO connection directly to mysql to fetch the Najma's rows and fix them, or better:
config(['database.default' => 'mysql']);
config(['database.connections.mysql.host' => env('DB_MASTER_HOST')]);
config(['database.connections.mysql.port' => env('DB_MASTER_PORT')]);
config(['database.connections.mysql.database' => env('DB_MASTER_DATABASE')]);
config(['database.connections.mysql.username' => env('DB_MASTER_USERNAME')]);
config(['database.connections.mysql.password' => env('DB_MASTER_PASSWORD')]);
\Illuminate\Support\Facades\DB::purge('mysql');
\Illuminate\Support\Facades\DB::setDefaultConnection('mysql');

// Run recalculator for NAJMA NAWALHUDAYA first to test
$student = \App\Models\Student::where('name', 'like', '%NAJMA%')->first();
if ($student) {
    echo "Running recalculator on VPS DB for student: " . $student->name . "\n";
    \App\Services\SaldoRecalculatorService::recalculateForStudent($student->id);
    
    // Output the top 5
    $histories = \App\Models\SaldoHistory::where('student_id', $student->id)->orderBy('created_at', 'desc')->take(5)->get();
    foreach($histories as $v) {
        echo $v->created_at . ' | AMT: ' . $v->amount . ' | BEF: ' . $v->balance_before . ' | AFT: ' . $v->balance_after . PHP_EOL;
    }
}

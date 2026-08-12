<?php
require __DIR__.'/bootstrap/app.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$student = \App\Models\Student::where('name', 'like', '%NAJMA NAWALHUDAYA%')->first();
$histories = \App\Models\SaldoHistory::where('student_id', $student->id)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->limit(10)->get(); 
foreach ($histories as $h) { 
    echo $h->id . ' | ' . $h->created_at . ' | AMT: ' . $h->amount . ' | BEFORE: ' . $h->balance_before . ' | AFTER: ' . $h->balance_after . " | " . substr($h->description, 0, 30) . "\n"; 
}

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$studentId = \App\Models\Student::where('nis', '330200377')->first()->id;

$t0 = microtime(true);
\Illuminate\Support\Facades\DB::transaction(function () use ($studentId) {
    \App\Services\TransactionService::cleanupGhostBillsForStudent($studentId);
    \App\Services\TransactionService::syncStudentBillsFromPaidTransactions($studentId);
    \App\Services\TransactionService::ensureStudentBillsSyncedFromRate($studentId, null);
});
$t1 = microtime(true);
echo "SyncStudentBillsJob took: " . round($t1 - $t0, 4) . "s\n";

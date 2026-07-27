<?php

$anomalies = App\Models\SaldoHistory::whereRaw('amount % 100 != 0')->get();
$totalAnomalies = $anomalies->count();

echo "Anomalies in SaldoHistory (amount not divisible by 100):\n";
echo "Count: $totalAnomalies\n\n";

if ($totalAnomalies > 0) {
    foreach ($anomalies->take(10) as $a) {
        echo "ID: {$a->id}, Type: {$a->type}, Amount: {$a->amount}, Date: {$a->created_at}\n";
    }
}

$studentAnomalies = App\Models\Student::whereRaw('saldo % 100 != 0')->get();
$totalStudentAnomalies = $studentAnomalies->count();

echo "\nAnomalies in Student (saldo not divisible by 100):\n";
echo "Count: $totalStudentAnomalies\n\n";

if ($totalStudentAnomalies > 0) {
    foreach ($studentAnomalies->take(10) as $a) {
        echo "ID: {$a->id}, NIS: {$a->nis}, Saldo: {$a->saldo}\n";
    }
}

$totalTopup = App\Models\SaldoHistory::where('type', App\Models\SaldoHistory::TYPE_IN)->where('status', App\Models\SaldoHistory::STATUS_SUCCESS)->sum('amount');
$totalPengurangan = App\Models\SaldoHistory::whereIn('type', [App\Models\SaldoHistory::TYPE_OUT, App\Models\SaldoHistory::TYPE_WITHDRAW])->where('status', App\Models\SaldoHistory::STATUS_SUCCESS)->sum('amount');
$saldoTersedia = App\Models\Student::sum('saldo');

echo "\nSummary:\n";
echo "Total Topup (IN, SUCCESS): " . $totalTopup . "\n";
echo "Total Pengurangan (OUT/WITHDRAW, SUCCESS): " . $totalPengurangan . "\n";
echo "Saldo Tersedia (Student sum saldo): " . $saldoTersedia . "\n";

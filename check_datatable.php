<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
require_once __DIR__.'/app/Helpers/helpers.php';

use App\Models\Transaction;
use App\Models\PaymentMethod;
use Yajra\DataTables\DataTables;

$transactions = Transaction::with('student', 'paymentMethod', 'activeProof.bank', 'admin')
    ->whereHas('paymentMethod', fn($query) => $query->where('type', PaymentMethod::TYPE_TRANSFER))
    ->where('type', Transaction::TYPE_BILL)
    ->whereIn('status', [Transaction::STATUS_PAID, Transaction::STATUS_REJECTED])
    ->where('is_deleted_from_archive', false)
    ->latest('updated_at');

$dt = DataTables::of($transactions)
    ->addColumn('proof', function ($transaction) {
        $proofUrl = $transaction->activeProof?->proof_image_url ?? $transaction->activeProof?->proof_image;
        return $proofUrl ? $proofUrl : '-';
    })
    ->addColumn('bank_recipient', function ($transaction) {
        $bank = $transaction->activeProof?->bank;
        return $bank ? $bank->name : '-';
    })
    ->make(true);

$data = json_decode($dt->getContent(), true);
echo "TOTAL RECORDS: " . $data['recordsTotal'] . "\n";
if (!empty($data['data'])) {
    for ($i=0; $i<min(5, count($data['data'])); $i++) {
        $row = $data['data'][$i];
        echo "Row " . ($i+1) . ":\n";
        echo "  - Student: " . ($row['student']['name'] ?? 'N/A') . "\n";
        echo "  - Bank Recipient: " . $row['bank_recipient'] . "\n";
        echo "  - Proof: " . $row['proof'] . "\n";
        echo "  - Raw Active Proof: " . json_encode($row['active_proof']) . "\n";
    }
}

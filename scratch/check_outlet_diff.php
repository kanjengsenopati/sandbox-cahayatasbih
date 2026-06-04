<?php
include __DIR__ . '/../vendor/autoload.php';
$app = include_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SaldoHistory;
use App\Models\PointOfSaleTransaction;

echo "=== ANALYZING POS TRANSACTIONS AND SALDO HISTORY ===\n";

// Get latest 50 POS-related SaldoHistory records
$saldoHistories = SaldoHistory::with('outlet', 'student')
    ->where('usage', SaldoHistory::USAGE_POS)
    ->latest()
    ->take(50)
    ->get();

echo "Analyzing " . count($saldoHistories) . " latest SaldoHistory POS records:\n";
$mismatchCount = 0;
$missingPosTxCount = 0;

foreach ($saldoHistories as $sh) {
    // Try to find the corresponding PointOfSaleTransaction
    // It can be linked via saldo_history_id in point_of_sale_transactions table
    $posTx = PointOfSaleTransaction::where('saldo_history_id', $sh->id)->first();
    
    if (!$posTx) {
        // Try fallback search: same student, same amount, within 2 minutes of created_at
        $posTx = PointOfSaleTransaction::where('student_id', $sh->student_id)
            ->where('pay_amount', $sh->amount)
            ->whereBetween('created_at', [
                $sh->created_at->copy()->subMinutes(2),
                $sh->created_at->copy()->addMinutes(2)
            ])
            ->first();
    }

    if (!$posTx) {
        $missingPosTxCount++;
        echo "ALERT: SaldoHistory ID " . $sh->id . " has no corresponding PointOfSaleTransaction!\n";
        echo "  - Time: " . $sh->created_at . "\n";
        echo "  - Student: " . ($sh->student?->name ?? 'N/A') . "\n";
        echo "  - Amount: " . $sh->amount . "\n";
        echo "  - Outlet: " . ($sh->outlet?->name ?? 'N/A') . " (ID: " . $sh->outlet_id . ")\n";
        echo "--------------------------------------------------\n";
    } else {
        // Compare outlet_id
        if ($sh->outlet_id !== $posTx->outlet_id) {
            $mismatchCount++;
            echo "OUTLET ID MISMATCH for SaldoHistory ID " . $sh->id . " & POS Transaction ID " . $posTx->id . "\n";
            echo "  - Time: " . $sh->created_at . "\n";
            echo "  - Student: " . ($sh->student?->name ?? 'N/A') . "\n";
            echo "  - SaldoHistory Outlet: " . ($sh->outlet?->name ?? 'N/A') . " (ID: " . $sh->outlet_id . ")\n";
            echo "  - POS Transaction Outlet: " . ($posTx->outlet?->name ?? 'N/A') . " (ID: " . $posTx->outlet_id . ")\n";
            echo "--------------------------------------------------\n";
        }
    }
}

echo "\nSummary:\n";
echo "- Total analyzed: " . count($saldoHistories) . "\n";
echo "- Missing POS Transactions: " . $missingPosTxCount . "\n";
echo "- Outlet ID Mismatches: " . $mismatchCount . "\n";

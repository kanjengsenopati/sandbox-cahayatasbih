<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

// SINGLE FIX REPLACEMENT
$singleOld = <<<'EOD'
        if ($cat === 'cat2') {
            DB::connection($conn)->table('bills')->where('id', $bill_id)->update(['status' => 'PAID', 'updated_at' => now()]);
            $snapshot = ['old_status' => 'UNPAID'];
            $msg = 'Sukses: Status tagihan berhasil divalidasi menjadi PAID.';
        } 
        elseif ($cat === 'cat3') {
            DB::connection($conn)->table('bills')->where('id', $bill_id)->update(['status' => 'UNPAID', 'updated_at' => now()]);
            $snapshot = ['old_status' => 'PAID'];
            $msg = 'Sukses: Status tagihan dikembalikan ke UNPAID.';
        }
EOD;

$singleNew = <<<'EOD'
        if ($cat === 'cat2') {
            $old = DB::connection($conn)->table('bills')->where('id', $bill_id)->first();
            DB::connection($conn)->table('bills')->where('id', $bill_id)->update(['status' => 'PAID', 'paid_amount' => DB::raw('amount'), 'updated_at' => now()]);
            $snapshot = ['old_status' => 'UNPAID', 'old_paid' => $old->paid_amount];
            $msg = 'Sukses: Status tagihan berhasil divalidasi menjadi PAID.';
        } 
        elseif ($cat === 'cat3') {
            $old = DB::connection($conn)->table('bills')->where('id', $bill_id)->first();
            $act = DB::connection($conn)->table('transaction_details')
                ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->where('transaction_details.bill_id', $bill_id)
                ->where('transactions.status', 'PAID')
                ->whereNull('transaction_details.deleted_at')
                ->whereNull('transactions.deleted_at')
                ->sum('transactions.pay_amount');
            DB::connection($conn)->table('bills')->where('id', $bill_id)->update(['status' => 'UNPAID', 'paid_amount' => $act, 'updated_at' => now()]);
            $snapshot = ['old_status' => 'PAID', 'old_paid' => $old->paid_amount];
            $msg = 'Sukses: Status tagihan dikembalikan ke UNPAID.';
        }
EOD;
$c = str_replace($singleOld, $singleNew, $c);


// MULTI FIX REPLACEMENT
$multiOld = <<<'EOD'
            if ($cat === 'cat2') {
                DB::connection($conn)->table('bills')->where('id', $bill_id)->update(['status' => 'PAID', 'updated_at' => now()]);
                $snapshot = ['old_status' => 'UNPAID'];
            } elseif ($cat === 'cat3') {
                DB::connection($conn)->table('bills')->where('id', $bill_id)->update(['status' => 'UNPAID', 'updated_at' => now()]);
                $snapshot = ['old_status' => 'PAID'];
            } elseif ($cat === 'cat5') {
EOD;

$multiNew = <<<'EOD'
            if ($cat === 'cat2') {
                $old = DB::connection($conn)->table('bills')->where('id', $bill_id)->first();
                DB::connection($conn)->table('bills')->where('id', $bill_id)->update(['status' => 'PAID', 'paid_amount' => DB::raw('amount'), 'updated_at' => now()]);
                $snapshot = ['old_status' => 'UNPAID', 'old_paid' => $old->paid_amount];
            } elseif ($cat === 'cat3') {
                $old = DB::connection($conn)->table('bills')->where('id', $bill_id)->first();
                $act = DB::connection($conn)->table('transaction_details')
                    ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
                    ->where('transaction_details.bill_id', $bill_id)
                    ->where('transactions.status', 'PAID')
                    ->whereNull('transaction_details.deleted_at')
                    ->whereNull('transactions.deleted_at')
                    ->sum('transactions.pay_amount');
                DB::connection($conn)->table('bills')->where('id', $bill_id)->update(['status' => 'UNPAID', 'paid_amount' => $act, 'updated_at' => now()]);
                $snapshot = ['old_status' => 'PAID', 'old_paid' => $old->paid_amount];
            } elseif ($cat === 'cat5') {
EOD;
$c = str_replace($multiOld, $multiNew, $c);


// ROLLBACK REPLACEMENT
$rollOld = <<<'EOD'
        if ($repair->category === 'cat2' || $repair->category === 'cat3') {
            DB::connection($conn)->table('bills')->where('id', $repair->bill_id)->update(['status' => $snap['old_status'], 'updated_at' => now()]);
        }
EOD;

$rollNew = <<<'EOD'
        if ($repair->category === 'cat2' || $repair->category === 'cat3') {
            DB::connection($conn)->table('bills')->where('id', $repair->bill_id)->update([
                'status' => $snap['old_status'], 
                'paid_amount' => $snap['old_paid'] ?? 0, 
                'updated_at' => now()
            ]);
        }
EOD;
$c = str_replace($rollOld, $rollNew, $c);

file_put_contents($f, $c);
echo "REPLACED\n";

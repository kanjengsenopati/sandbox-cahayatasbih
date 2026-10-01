<?php
$content = file_get_contents('app/Services/MasterIngestionBridgeService.php');

$search = <<<EOD
                                        // Also ingest saldo history if paid via SALDO
                                        if (\$mTx->payment_method_id) {
                                            \$mSaldoHist = \$masterConn->table('saldo_histories')
                                                ->where('transaction_id', \$mTx->id)
                                                ->get();
                                            foreach (\$mSaldoHist as \$sh) {
                                                if (!\$localConn->table('saldo_histories')->where('id', \$sh->id)->exists()) {
                                                    \$localConn->table('saldo_histories')->insert((array) \$sh);
                                                }
                                            }
                                        }
EOD;

$replace = <<<EOD
                                        // Also ingest saldo history if paid via SALDO
                                        if (\$mTx->payment_method_id) {
                                            try {
                                                // Attempt to find by description or amount since transaction_id is missing
                                                \$mSaldoHist = \$masterConn->table('saldo_histories')
                                                    ->where('student_id', \$mTx->student_id)
                                                    ->where('amount', \$mTx->amount)
                                                    ->where('created_at', '>=', \$mTx->created_at)
                                                    ->get();
                                                foreach (\$mSaldoHist as \$sh) {
                                                    if (!\$localConn->table('saldo_histories')->where('id', \$sh->id)->exists()) {
                                                        \$localConn->table('saldo_histories')->insert((array) \$sh);
                                                    }
                                                }
                                            } catch (\Exception \$e) {
                                                // Log securely, don't crash the bill update
                                                \Illuminate\Support\Facades\Log::warning("Could not sync saldo_histories for tx {\$mTx->id}: " . \$e->getMessage());
                                            }
                                        }
EOD;

$content = str_replace($search, $replace, $content);
file_put_contents('app/Services/MasterIngestionBridgeService.php', $content);
echo "Fixed crash\n";

<?php

$content = file_get_contents('app/Services/MasterIngestionBridgeService.php');

$search = <<<EOD
                            // Temukan bill lokal berdasarkan student_id, mapped bill_type_id, dan month
                            \$localBill = \$localConn->table('bills')
                                ->where('student_id', \$mBill->student_id)
                                ->where('bill_type_id', \$targetBillTypeId)
                                ->where('month', \$mBill->month)
                                ->first();

                            \$targetBillId = \$mBill->id;

                            if (\$localBill) {
                                \$targetBillId = \$localBill->id;
                                unset(\$row['id']);
                                \$localConn->table('bills')->where('id', \$targetBillId)->update(\$row);
                            } else {
                                \$targetBillId = \$row['id'];
                                \$localConn->table('bills')->insert(\$row);
                            }
EOD;

$replace = <<<EOD
                            // Injeksi manual paid_amount karena database Master tidak memiliki field paid_amount
                            if (isset(\$row['status'])) {
                                if (\$row['status'] === 'PAID') {
                                    \$row['paid_amount'] = isset(\$row['amount']) ? \$row['amount'] : 0;
                                } elseif (\$row['status'] === 'UNPAID') {
                                    \$row['paid_amount'] = 0;
                                }
                            }

                            // Temukan bill lokal berdasarkan student_id, mapped bill_type_id, dan month
                            \$localBill = \$localConn->table('bills')
                                ->where('student_id', \$mBill->student_id)
                                ->where('bill_type_id', \$targetBillTypeId)
                                ->where('month', \$mBill->month)
                                ->first();

                            \$targetBillId = \$mBill->id;

                            if (\$localBill) {
                                \$targetBillId = \$localBill->id;
                                unset(\$row['id']);
                                \$localConn->table('bills')->where('id', \$targetBillId)->update(\$row);
                            } else {
                                \$targetBillId = \$row['id'];
                                \$localConn->table('bills')->insert(\$row);
                            }
EOD;

$content = str_replace($search, $replace, $content);

file_put_contents('app/Services/MasterIngestionBridgeService.php', $content);
echo "Updated MasterIngestionBridgeService.php with paid_amount logic\n";

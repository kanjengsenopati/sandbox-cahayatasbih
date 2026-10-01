<?php
$content = file_get_contents('app/Services/MasterIngestionBridgeService.php');

$search = <<<EOD
                            // Temukan bill lokal berdasarkan student_id, mapped bill_type_id, dan month
                            \$localBill = \$localConn->table('bills')
                                ->where('student_id', \$mBill->student_id)
                                ->where('bill_type_id', \$targetBillTypeId)
                                ->where('month', \$mBill->month)
                                ->first();
EOD;

$replace = <<<EOD
                            // Temukan bill lokal berdasarkan student_id, mapped bill_type_id, dan month
                            \$localBill = \$localConn->table('bills')
                                ->where('student_id', \$mBill->student_id)
                                ->where('bill_type_id', \$targetBillTypeId)
                                ->where('month', \$mBill->month)
                                ->whereNull('deleted_at')
                                ->first();
EOD;

$content = str_replace($search, $replace, $content);
file_put_contents('app/Services/MasterIngestionBridgeService.php', $content);
echo "Added whereNull('deleted_at') for localBill lookup\n";

<?php

$content = file_get_contents('app/Services/MasterIngestionBridgeService.php');

$search = <<<EOD
                            if (\$localBill) {
                                \$targetBillId = \$localBill->id;
                                unset(\$row['id']);
                                \$localConn->table('bills')->where('id', \$targetBillId)->update(\$row);
                            } else {
EOD;

$replace = <<<EOD
                            if (\$localBill) {
                                \$targetBillId = \$localBill->id;
                                unset(\$row['id']);
                                file_put_contents('debug_sync.log', json_encode(\$row) . PHP_EOL, FILE_APPEND);
                                \$localConn->table('bills')->where('id', \$targetBillId)->update(\$row);
                            } else {
EOD;

$content = str_replace($search, $replace, $content);
file_put_contents('app/Services/MasterIngestionBridgeService.php', $content);
echo "Added debug log\n";

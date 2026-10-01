<?php
$content = file_get_contents('app/Services/MasterIngestionBridgeService.php');

$search = <<<EOD
                              // Injeksi manual paid_amount karena database Master tidak memiliki field paid_amount
EOD;

$replace = <<<EOD
                              file_put_contents('debug_mapping.log', "Mapped \$mBill->bill_type_id to \$targetBillTypeId for month \$mBill->month\n", FILE_APPEND);
                              // Injeksi manual paid_amount karena database Master tidak memiliki field paid_amount
EOD;

$content = str_replace($search, $replace, $content);
file_put_contents('app/Services/MasterIngestionBridgeService.php', $content);
echo "Added mapping debug log\n";

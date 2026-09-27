<?php
$content = file_get_contents('app/Services/MasterIngestionBridgeService.php');

$search = <<<EOD
                    // Handle duplicate bills in Master DB: prioritize PAID status
                    \$rawMasterBills = \$mQuery->get();
EOD;

$replace = <<<EOD
                    // Handle duplicate bills in Master DB: prioritize PAID status
                    \$rawMasterBills = \$mQuery->get();
                    file_put_contents('debug_counts.log', "student: " . \$mRec->id . " - rawMasterBills: " . count(\$rawMasterBills) . "\n", FILE_APPEND);
EOD;

$content = str_replace($search, $replace, $content);
file_put_contents('app/Services/MasterIngestionBridgeService.php', $content);
echo "Added count debug log\n";

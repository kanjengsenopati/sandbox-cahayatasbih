<?php

$content = file_get_contents('app/Services/MasterIngestionBridgeService.php');

$search = <<<EOD
                    if (!empty(\$billTypeId)) {
                        \$mQuery->where('bill_type_id', \$billTypeId);
                    }
                    \$masterBills = \$mQuery->get();

                    if (\$masterBills->isNotEmpty()) {
                        foreach (\$masterBills as \$mBill) {
EOD;

$replace = <<<EOD
                    if (!empty(\$billTypeId)) {
                        \$mQuery->where('bill_type_id', \$billTypeId);
                    }
                    // Handle duplicate bills in Master DB: prioritize PAID status
                    \$rawMasterBills = \$mQuery->get();
                    \$masterBills = [];
                    foreach (\$rawMasterBills as \$b) {
                        if (!isset(\$masterBills[\$b->month])) {
                            \$masterBills[\$b->month] = \$b;
                        } else {
                            if (\$b->status === 'PAID') {
                                \$masterBills[\$b->month] = \$b;
                            }
                        }
                    }

                    if (!empty(\$masterBills)) {
                        foreach (\$masterBills as \$mBill) {
EOD;

$content = str_replace($search, $replace, $content);

file_put_contents('app/Services/MasterIngestionBridgeService.php', $content);
echo "Updated MasterIngestionBridgeService.php with deduplication logic\n";

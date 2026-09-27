<?php
$content = file_get_contents('app/Services/MasterIngestionBridgeService.php');

$search = <<<EOD
            if (!\$localRec) {
                \$status = 'NEW_RECORD';
                \$result['status_summary']['new_count']++;
                \$diffInfo['local']['is_empty'] = true;
                \$diffs['Status Tagihan Siswa'] = \$diffInfo;
            } else {
                if (\$mPaidCount !== \$lPaidCount || \$mBillsCount !== \$lBillsCount || \$mPaidAmount !== \$lPaidAmount) {
                    \$status = 'UPDATE_REQUIRED';
                    \$result['status_summary']['update_count']++;
                    \$diffs['Status Tagihan Siswa'] = \$diffInfo;
                } else {
                    \$result['status_summary']['match_count']++;
                }
            }
EOD;

$replace = <<<EOD
            if (!\$localRec) {
                \$status = 'NEW_RECORD';
                \$result['status_summary']['new_count']++;
                \$diffInfo['local']['is_empty'] = true;
            } else {
                if (\$mPaidCount !== \$lPaidCount || \$mBillsCount !== \$lBillsCount || \$mPaidAmount !== \$lPaidAmount) {
                    \$status = 'UPDATE_REQUIRED';
                    \$result['status_summary']['update_count']++;
                } else {
                    \$result['status_summary']['match_count']++;
                }
            }
            
            // ALWAYS assign diffInfo for billing_status so the UI can render the monthly cards even if EXACT_MATCH
            \$diffs['Status Tagihan Siswa'] = \$diffInfo;
EOD;

$content = str_replace(str_replace("\r\n", "\n", $search), $replace, str_replace("\r\n", "\n", $content));
file_put_contents('app/Services/MasterIngestionBridgeService.php', $content);
echo "Patched BridgeService to always assign diffInfo\n";

<?php

$content = file_get_contents('app/Services/MasterIngestionBridgeService.php');

$search = <<<EOD
                    if (\$module === 'billing_status') {
                        // Ingest / upsert bills for selected student IDs matching filters
                        \$mQuery = \$masterConn->table('bills')->where('student_id', \$mRec->id);
EOD;

$replace = <<<EOD
                    if (\$module === 'billing_status') {
                        // Ingest / upsert bills for selected student IDs matching filters
                        \$mQuery = \$masterConn->table('bills')->where('student_id', \$mRec->id)->whereNull('deleted_at');
EOD;

$content = str_replace($search, $replace, $content);

file_put_contents('app/Services/MasterIngestionBridgeService.php', $content);
echo "Added whereNull('deleted_at') to executeVerifiedMerge\n";

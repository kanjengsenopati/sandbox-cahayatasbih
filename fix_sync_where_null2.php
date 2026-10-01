<?php
$content = file_get_contents('app/Services/MasterIngestionBridgeService.php');
$content = str_replace(
    "\$mQuery = \$masterConn->table('bills')->where('student_id', \$mRec->id);",
    "\$mQuery = \$masterConn->table('bills')->where('student_id', \$mRec->id)->whereNull('deleted_at');",
    $content
);
file_put_contents('app/Services/MasterIngestionBridgeService.php', $content);
echo "Fixed whereNull\n";

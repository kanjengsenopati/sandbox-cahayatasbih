<?php
$content = file_get_contents('resources/views/admins/admin/audit/sync.blade.php');

$search = <<<EOD
<<<<<<< HEAD

'<th class="text-center">Status</th>' +
=======
'<th class="cursor-pointer text-primary text-center" onclick="sortSyncItemsBySyncStatus()" style="cursor: pointer;" title="Klik untuk mengurutkan berdasarkan Status Sinkronisasi">Status <i class="fas fa-sort ms-1" id="sort-icon-sync-status"></i></th>' +
>>>>>>> f2874e0 (feat(sync): preserve UI filter state after sync and auto-sort status column)
EOD;

$replace = <<<EOD
'<th class="cursor-pointer text-primary text-center" onclick="sortSyncItemsBySyncStatus()" style="cursor: pointer;" title="Klik untuk mengurutkan berdasarkan Status Sinkronisasi">Status <i class="fas fa-sort ms-1" id="sort-icon-sync-status"></i></th>' +
EOD;

$content = str_replace(str_replace("\r\n", "\n", $search), str_replace("\r\n", "\n", $replace), str_replace("\r\n", "\n", $content));
file_put_contents('resources/views/admins/admin/audit/sync.blade.php', $content);
echo "Resolved merge conflict\n";

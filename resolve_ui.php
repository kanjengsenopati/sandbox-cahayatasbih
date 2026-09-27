<?php
$content = file_get_contents('resources/views/admins/admin/audit/sync.blade.php');

$search = <<<EOD
<<<<<<< HEAD


'<th class="text-center">Status</th>' +

'<th class="cursor-pointer text-primary" onclick="sortSyncItemsByStatus()" style="cursor: pointer;" title="Klik untuk mengurutkan Identik / Butuh Sync">Perbandingan Status Tagihan (Aplikasi Lama &rarr; Lokal) <i class="fas fa-sort ms-1" id="sort-icon-status"></i></th>' +
=======
'<th class="cursor-pointer text-primary" onclick="sortSyncItemsByStatus()" style="cursor: pointer;" title="Klik untuk mengurutkan Identik / Butuh Sync">Perbandingan Tagihan (Lama &rarr; Lokal) <i class="fas fa-sort ms-1" id="sort-icon-status"></i></th>' +
>>>>>>> 443fb2e (fix(sync): ui refinements and state preservation enhancements)
EOD;

$replace = <<<EOD
'<th class="cursor-pointer text-primary" onclick="sortSyncItemsByStatus()" style="cursor: pointer;" title="Klik untuk mengurutkan Identik / Butuh Sync">Perbandingan Tagihan (Lama &rarr; Lokal) <i class="fas fa-sort ms-1" id="sort-icon-status"></i></th>' +
EOD;

$content = str_replace(str_replace("\r\n", "\n", $search), str_replace("\r\n", "\n", $replace), str_replace("\r\n", "\n", $content));
file_put_contents('resources/views/admins/admin/audit/sync.blade.php', $content);
echo "Resolved UI merge conflict\n";

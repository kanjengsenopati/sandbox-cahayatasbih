<?php
$content = file_get_contents('resources/views/admins/admin/audit/sync.blade.php');

$pattern = '/\'<th>Nama Record<\/th>\' \+\s*\'<th class="cursor-pointer text-primary"/';
$replacement = <<<EOD
'<th>Nama Record</th>' +
'<th class="text-center">Status</th>' +
'<th class="cursor-pointer text-primary"
EOD;

$content = preg_replace($pattern, $replacement, $content, 1);
file_put_contents('resources/views/admins/admin/audit/sync.blade.php', $content);
echo "Fixed thead\n";

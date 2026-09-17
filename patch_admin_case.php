<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

$oldPHP = <<<'EOD'
$admin_name = !empty($item->admin_petugas) ? $item->admin_petugas : 'Sistem / Admin';
EOD;

$newPHP = <<<'EOD'
$admin_name = !empty($item->admin_petugas) ? ucwords(strtolower($item->admin_petugas)) : 'Sistem / Admin';
EOD;

$c = str_replace($oldPHP, $newPHP, $c);

file_put_contents($f, $c);
echo "TITLE_CASE_ADMIN_PATCHED\n";

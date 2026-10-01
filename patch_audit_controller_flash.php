<?php
$content = file_get_contents('app/Http/Controllers/Admin/AuditController.php');

$search = <<<EOD
        if (\$result['status'] === 'success') {
            return redirect()->back()->with('success', \$result['message']);
        }
EOD;

$replace = <<<EOD
        if (\$result['status'] === 'success') {
            return redirect()->back()->with('success', \$result['message'])->with('synced_ids', \$selectedIds);
        }
EOD;

$content = str_replace($search, $replace, $content);
file_put_contents('app/Http/Controllers/Admin/AuditController.php', $content);
echo "Patched AuditController to flash synced_ids\n";

<?php
$content = file_get_contents('resources/views/admins/admin/audit/sync.blade.php');

// We need to strip out JS comments (//) from the script section.
// A simple regex to remove lines that start with optional whitespace and //
$content = preg_replace('/^\s*\/\/.*$/m', '', $content);

file_put_contents('resources/views/admins/admin/audit/sync.blade.php', $content);
echo "Comments removed!\n";

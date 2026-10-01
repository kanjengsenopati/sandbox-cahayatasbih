<?php
$content = file_get_contents('resources/views/admins/admin/audit/sync.blade.php');

$content = str_replace(
    'data-academic-year-id="{{ $bt->academic_year_id ?? \'\' }}"',
    'data-academic-year-id="{{ $bt->academic_year_id ?? \'\' }}" data-school-id="{{ $bt->school_id ?? \'\' }}"',
    $content
);

file_put_contents('resources/views/admins/admin/audit/sync.blade.php', $content);
echo "Done 1\n";

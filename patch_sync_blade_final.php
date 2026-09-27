<?php
$content = file_get_contents('resources/views/admins/admin/audit/sync.blade.php');

// 1. Inject recentlySyncedIds
if (strpos($content, 'window.recentlySyncedIds') === false) {
    $searchJs = "<script>\n    // Global configuration";
    $replaceJs = "<script>\n    window.recentlySyncedIds = @json(session('synced_ids', []));\n    // Global configuration";
    $content = str_replace($searchJs, $replaceJs, $content);
}

// 2. Modify renderMonthlyCards definition
$pattern = '/function renderMonthlyCards\(info\) \{/s';
$replacement = 'function renderMonthlyCards(info, isSynced) {';
$content = preg_replace($pattern, $replacement, $content, 1);

// 3. Modify renderMonthlyCards inner local row
$pattern2 = '/var bg = \'bg-light text-muted\';\s*if \(isPaid\) bg = \'bg-success text-white\';\s*else if \(isUnpaid\) bg = \'bg-light-danger text-danger\';\s*html \+= \'<div class="badge rounded px-2 py-1 fs-9 fw-bolder \' \+ bg \+ \'" style="width:32px;">\' \+ getMonthAbbr\(m\) \+ \'<\/div>\';/s';
$replacement2 = <<<EOD
var bg = 'bg-light text-muted';
            if (isPaid) bg = isSynced ? 'text-white' : 'bg-success text-white';
            else if (isUnpaid) bg = 'bg-light-danger text-danger';
            
            var extraStyle = (isPaid && isSynced) ? 'background-color: #8b5cf6;' : ''; // Purple
            html += '<div class="badge rounded px-2 py-1 fs-9 fw-bolder ' + bg + '" style="width:32px; ' + extraStyle + '">' + getMonthAbbr(m) + '</div>';
EOD;
$content = preg_replace($pattern2, $replacement2, $content, 1); // Only replaces the second occurrence because the first is master row. But wait, we need to ensure it only affects Local row!
// ACTUALLY, it might replace the Master row if regex matches the first occurrence!
// Let's rewrite this part manually using explode.

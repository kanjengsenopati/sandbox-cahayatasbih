<?php
$content = file_get_contents('resources/views/admins/admin/audit/sync.blade.php');
preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $content, $matches);
foreach($matches[1] as $idx => $script) {
    // Replace the entire window.recentlySyncedIds line with a valid var assignment
    $script = preg_replace('/window\.recentlySyncedIds\s*=\s*@json\(.*?\);/', 'window.recentlySyncedIds = [];', $script);
    $script = preg_replace('/\{\{.*?\}\}/', '""', $script);
    
    file_put_contents('script_' . $idx . '.js', $script);
    exec('node -c script_' . $idx . '.js 2>&1', $out, $code);
    if($code !== 0) {
        echo "Syntax error in script $idx:\n";
        echo implode("\n", $out) . "\n";
    } else {
        echo "Script $idx OK!\n";
    }
    $out = [];
}

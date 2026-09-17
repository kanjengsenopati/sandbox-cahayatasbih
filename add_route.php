<?php
$f = 'routes/web.php';
$lines = file($f);
if (strpos(end($lines), 'audit-vps-data') !== false) {
    array_pop($lines);
    if (strpos(end($lines), '\n') !== false || trim(end($lines)) == '') {
        array_pop($lines);
    }
}
$content = implode("", $lines);
$content .= "\nRoute::any('/audit-vps-data', function() {\n    return require public_path('audit_vps.php');\n})->middleware('web');\n";
file_put_contents($f, $content);

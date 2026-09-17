<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);
$c = str_replace(
    'if ($_SERVER[\'REQUEST_METHOD\'] === \'POST\'', 
    'if (isset($_SERVER[\'REQUEST_METHOD\']) && $_SERVER[\'REQUEST_METHOD\'] === \'POST\'', 
    $c
);
file_put_contents($f, $c);
echo "FIXED_REQUEST_METHOD\n";

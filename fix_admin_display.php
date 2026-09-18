<?php
$files = [
    'resources/views/admins/bill/table/body-kilat.blade.php',
    'resources/views/admins/bill/table/body-lainnya.blade.php'
];

foreach ($files as $f) {
    if (!file_exists($f)) continue;
    
    $content = file_get_contents($f);
    
    $content = str_replace(
        "{{ \$detailPayment->admin->name ?? \$detailPayment->user->name ?? 'Admin' }}",
        "{{ \$detailPayment->admin->name ?? 'Sistem / Admin' }}",
        $content
    );

    file_put_contents($f, $content);
}
echo "REPLACED\n";

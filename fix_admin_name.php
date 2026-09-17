<?php
$files = [
    'resources/views/admins/bill/table/body-kilat.blade.php',
    'resources/views/admins/bill/table/body-lainnya.blade.php'
];

foreach ($files as $f) {
    if (!file_exists($f)) continue;
    
    $content = file_get_contents($f);
    
    // Pattern for body-kilat
    $content = str_replace(
        "@if(strtoupper(\$billDetail?->payment_method ?? '') == 'TUNAI' || strtoupper(\$billDetail?->payment_method ?? '') == 'CASH')",
        "@if(strtoupper(\$billDetail?->payment_method ?? '') == 'TUNAI' || strtoupper(\$billDetail?->payment_method ?? '') == 'CASH' || !empty(\$detailPayment?->admin_id))",
        $content
    );
    
    // Pattern for body-lainnya
    $content = str_replace(
        "@if(strtoupper(\$billDetail->payment_method) == 'TUNAI' || strtoupper(\$billDetail->payment_method) == 'CASH')",
        "@if(strtoupper(\$billDetail->payment_method) == 'TUNAI' || strtoupper(\$billDetail->payment_method) == 'CASH' || !empty(\$detailPayment?->admin_id))",
        $content
    );

    file_put_contents($f, $content);
}
echo "REPLACED\n";

<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

// Update santri names to title case
$c = str_replace(
    '<?= htmlspecialchars($g[\'santri\']) ?>',
    '<?= htmlspecialchars(ucwords(strtolower($g[\'santri\']))) ?>',
    $c
);

$c = str_replace(
    '<?= htmlspecialchars($g[\'kelas\']) ?>',
    '<?= htmlspecialchars(ucwords(strtolower($g[\'kelas\']))) ?>',
    $c
);

$c = str_replace(
    '<?= htmlspecialchars($tagihan_name) ?>',
    '<?= htmlspecialchars(ucwords(strtolower($tagihan_name))) ?>',
    $c
);

// Update hardcoded uppercase text to title case
$c = str_replace('Kategori 2: Missed Update (Sudah Bayar Tapi Status Belum Terbayar)', 'Kategori 2: Missed Update (Sudah Bayar tapi Status Belum Terbayar)', $c);
$c = str_replace('Kategori 3: Partial Paid (Sudah Bayar Sebagian Tapi Status Lunas)', 'Kategori 3: Partial Paid (Sudah Bayar Sebagian tapi Status Lunas)', $c);
$c = str_replace('Kategori 5: Duplikasi Transaksi (Ada Transaksi Ganda/Berulang)', 'Kategori 5: Duplikasi Transaksi (Ada Transaksi Ganda/Berulang)', $c);

// Fix CSS capitalize on elements that don't need it or need it differently
$c = preg_replace('/capitalize tracking-widest/i', 'capitalize tracking-widest', $c);

file_put_contents($f, $c);
echo "TITLE_CASE_PATCHED\n";

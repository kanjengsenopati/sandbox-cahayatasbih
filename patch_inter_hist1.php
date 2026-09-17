<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

// 1. ADD INTER FONT
$head = '</head>';
$interFont = <<<EOD
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body, div, span, h1, h2, h3, h4, h5, h6, p, a, button, input { font-family: 'Inter', sans-serif !important; }
</style>
</head>
EOD;
$c = str_replace($head, $interFont, $c);

// 2. REMOVE UPPERCASE CLASSES
$c = str_replace(' uppercase ', ' capitalize ', $c);
$c = str_replace('uppercase tracking-wider', 'capitalize tracking-wider', $c);
$c = str_replace('uppercase tracking-widest', 'capitalize tracking-widest', $c);
$c = preg_replace('/\buppercase\b/', 'capitalize', $c);

// 3. FIX HARDCODED TEXTS TO TITLE CASE
$c = str_replace('UPDATE KOREKSI TERPILIH', 'Update Koreksi Terpilih', $c);
$c = str_replace('UPDATE KOREKSI', 'Update Koreksi', $c);
$c = str_replace('PILIH SEMUA', 'Pilih Semua', $c);
$c = str_replace('BATALKAN', 'Batalkan', $c);

// 4. FIX STUDENT AND TAGIHAN HASH COLLISIONS
$c = str_replace("md5(\$g['santri'].\$g['kelas'])", "md5(\$anomaly_category.\$g['santri'].\$g['kelas'])", $c);
$c = str_replace("md5(\$g['santri'].\$tagihan_name)", "md5(\$anomaly_category.\$g['santri'].\$tagihan_name)", $c);

// 5. ADD MULTI-REVERT BACKEND LOGIC
$backendRevert = <<<'EOD'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'revert_repair') {
EOD;
$backendRevertNew = <<<'EOD'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'revert_repair_multi') {
    header('Content-Type: application/json');
    $items = json_decode($_POST['items'] ?? '[]', true);
    if (empty($items)) { echo json_encode(['success' => false, 'message' => 'No items']); exit; }

    DB::connection($conn)->beginTransaction();
    try {
        $count = 0;
        foreach($items as $repair_id) {
            $repair = DB::connection($conn)->table('anomaly_repairs')->where('id', $repair_id)->first();
            if (!$repair || $repair->reverted_at) continue;
            
            $snap = json_decode($repair->snapshot, true);
            if ($repair->category === 'cat2' || $repair->category === 'cat3') {
                DB::connection($conn)->table('bills')->where('id', $repair->bill_id)->update([
                    'status' => $snap['old_status'], 
                    'paid_amount' => $snap['old_paid'] ?? 0, 
                    'updated_at' => now()
                ]);
            } elseif ($repair->category === 'cat5') {
                if(!empty($snap['deleted_txs'])) {
                    foreach($snap['deleted_txs'] as $tx_id) {
                        DB::connection($conn)->table('transactions')->where('id', $tx_id)->update(['deleted_at' => null, 'status' => 'PAID', 'updated_at' => now()]);
                    }
                }
                if(!empty($snap['deleted_tds'])) {
                    foreach($snap['deleted_tds'] as $td_id) {
                        DB::connection($conn)->table('transaction_details')->where('id', $td_id)->update(['deleted_at' => null, 'updated_at' => now()]);
                    }
                }
            }
            DB::connection($conn)->table('anomaly_repairs')->where('id', $repair_id)->update(['reverted_at' => now()]);
            $count++;
        }
        DB::connection($conn)->commit();
        echo json_encode(['success' => true, 'message' => "Berhasil membatalkan $count perbaikan secara kolektif!"]);
    } catch (\Exception $e) {
        DB::connection($conn)->rollBack();
        echo json_encode(['success' => false, 'message' => 'Gagal Rollback: ' . $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'revert_repair') {
EOD;
$c = str_replace($backendRevert, $backendRevertNew, $c);

// 6. ADD MULTI-REVERT JS LOGIC
$jsRevert = <<<'EOD'
function revertRepair(repairId, btn) {
EOD;
$jsRevertNew = <<<'EOD'
function revertMultiRepair(stuHash, btn) {
    const checkboxes = document.querySelectorAll('.chk-group-' + stuHash + ':checked');
    if (checkboxes.length === 0) return;
    
    if(!confirm('Anda yakin ingin membatalkan kolektif (' + checkboxes.length + ' kartu) yang dipilih?')) return;
    
    const items = [];
    checkboxes.forEach(cb => {
        items.push(cb.value);
    });
    
    const oldText = btn.innerHTML;
    btn.innerHTML = 'Memproses...';
    btn.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'revert_repair_multi');
    formData.append('items', JSON.stringify(items));
    
    fetch('audit_vps.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) { alert(data.message); location.reload(); }
        else { alert('Error: ' + data.message); btn.innerHTML = oldText; btn.disabled = false; }
    })
    .catch(err => { alert('Kesalahan jaringan'); btn.innerHTML = oldText; btn.disabled = false; });
}

function revertRepair(repairId, btn) {
EOD;
$c = str_replace($jsRevert, $jsRevertNew, $c);

// 7. GROUP HISTORY DATA
// We must find where `$history_repairs` is executed.
$histQuery = <<<'EOD'
$history_repairs = DB::connection($conn)->select("
    SELECT ar.id as repair_id, ar.category, ar.created_at as repair_time, 
           b.id as bill_id, s.name as santri, COALESCE(c.name,'-') as kelas, bt.name as tagihan, 
           b.month, b.year, b.amount
    FROM anomaly_repairs ar
    JOIN bills b ON b.id = ar.bill_id
    JOIN students s ON s.id = b.student_id
    LEFT JOIN classrooms c ON c.id = s.classroom_id
    JOIN bill_types bt ON bt.id = b.bill_type_id
    WHERE ar.reverted_at IS NULL
    ORDER BY ar.created_at DESC
");
EOD;
$histQueryNew = $histQuery . "\n\$g_history = groupDataBySantriAndTagihan(\$history_repairs);\n";
$c = str_replace($histQuery, $histQueryNew, $c);

file_put_contents($f, $c);
echo "PATCH_INITIALIZED\n";

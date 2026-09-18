<?php
$f = 'public/audit_vps.php';
$c = file_get_contents($f);

// =============================================
// 1. ADD BACKEND ENDPOINT: fix_all_cat2
// =============================================
$insertBefore = "// --- AJAX HANDLER FOR Update Koreksi ---";

$newEndpoint = <<<'ENDPOINT'
// --- AJAX HANDLER FOR Massive Bulk Fix CAT2 ---
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'fix_all_cat2') {
    header('Content-Type: application/json');
    $offset = (int)($_POST['offset'] ?? 0);
    $limit = (int)($_POST['limit'] ?? 50);

    try {
        // Fresh query: ambil bill_id CAT2 yang BELUM pernah diperbaiki
        $bills = DB::connection($conn)->select("
            SELECT DISTINCT b.id AS bill_id, b.paid_amount AS old_paid
            FROM bills b
            JOIN transaction_details td ON td.bill_id = b.id
            JOIN transactions t ON t.id = td.transaction_id
            JOIN academic_years ay ON ay.id = b.academic_year_id
            WHERE b.deleted_at IS NULL AND b.status = 'UNPAID' AND ay.name = '2026/2027'
            AND td.deleted_at IS NULL AND t.deleted_at IS NULL AND t.status = 'PAID'
            ORDER BY b.id
            LIMIT $limit OFFSET 0
        ");
        // NOTE: OFFSET selalu 0 karena setiap batch yang sukses mengubah status UNPAID->PAID,
        // sehingga query berikutnya secara otomatis mengambil batch selanjutnya (idempotent).

        if (empty($bills)) {
            // Hitung sisa untuk konfirmasi
            $remaining = DB::connection($conn)->selectOne("
                SELECT COUNT(DISTINCT b.id) as cnt
                FROM bills b
                JOIN transaction_details td ON td.bill_id = b.id
                JOIN transactions t ON t.id = td.transaction_id
                JOIN academic_years ay ON ay.id = b.academic_year_id
                WHERE b.deleted_at IS NULL AND b.status = 'UNPAID' AND ay.name = '2026/2027'
                AND td.deleted_at IS NULL AND t.deleted_at IS NULL AND t.status = 'PAID'
            ")->cnt;
            echo json_encode(['success' => true, 'processed' => 0, 'remaining' => (int)$remaining, 'message' => 'Tidak ada lagi anomali CAT2.']);
            exit;
        }

        DB::connection($conn)->beginTransaction();
        $processed = 0;
        foreach ($bills as $bill) {
            DB::connection($conn)->table('bills')->where('id', $bill->bill_id)->update([
                'status' => 'PAID',
                'paid_amount' => DB::raw('amount'),
                'updated_at' => now()
            ]);
            DB::connection($conn)->table('anomaly_repairs')->insert([
                'bill_id' => $bill->bill_id,
                'category' => 'cat2',
                'snapshot' => json_encode(['old_status' => 'UNPAID', 'old_paid' => $bill->old_paid]),
                'created_at' => now()
            ]);
            $processed++;
        }
        DB::connection($conn)->commit();

        // Hitung sisa setelah batch ini
        $remaining = DB::connection($conn)->selectOne("
            SELECT COUNT(DISTINCT b.id) as cnt
            FROM bills b
            JOIN transaction_details td ON td.bill_id = b.id
            JOIN transactions t ON t.id = td.transaction_id
            JOIN academic_years ay ON ay.id = b.academic_year_id
            WHERE b.deleted_at IS NULL AND b.status = 'UNPAID' AND ay.name = '2026/2027'
            AND td.deleted_at IS NULL AND t.deleted_at IS NULL AND t.status = 'PAID'
        ")->cnt;

        echo json_encode([
            'success' => true,
            'processed' => $processed,
            'remaining' => (int)$remaining,
            'message' => "Batch selesai: $processed tagihan diperbaiki."
        ]);
    } catch (\Exception $e) {
        DB::connection($conn)->rollBack();
        echo json_encode(['success' => false, 'message' => 'Gagal batch: ' . $e->getMessage()]);
    }
    exit;
}

// --- AJAX HANDLER FOR Pre-count CAT2 ---
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'count_cat2') {
    header('Content-Type: application/json');
    $total = DB::connection($conn)->selectOne("
        SELECT COUNT(DISTINCT b.id) as cnt
        FROM bills b
        JOIN transaction_details td ON td.bill_id = b.id
        JOIN transactions t ON t.id = td.transaction_id
        JOIN academic_years ay ON ay.id = b.academic_year_id
        WHERE b.deleted_at IS NULL AND b.status = 'UNPAID' AND ay.name = '2026/2027'
        AND td.deleted_at IS NULL AND t.deleted_at IS NULL AND t.status = 'PAID'
    ")->cnt;
    echo json_encode(['success' => true, 'total' => (int)$total]);
    exit;
}

ENDPOINT;

$c = str_replace($insertBefore, $newEndpoint . "\n" . $insertBefore, $c);

file_put_contents($f, $c);
echo "BACKEND ENDPOINT ADDED\n";

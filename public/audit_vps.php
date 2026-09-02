<?php
/**
 * UI Viewer & Excel Exporter untuk Anomali Pembayaran VPS
 * URL: http://localhost:8000/audit_vps.php
 * FILTER: Hanya untuk Tahun Ajaran 2026/2027 (Semua UPT)
 */

// --- SUPPRESS DEPRECATION WARNINGS FOR VPS ---
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');

if (!function_exists('app')) {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
}

// --- OVERRIDE PHP TIMEOUT ---
// Karena query mengambil data agregasi dari VPS jarak jauh, 
// eksekusi bisa memakan waktu lebih dari 30 detik.
ini_set('max_execution_time', '0');
set_time_limit(0);
// ----------------------------

use Illuminate\Support\Facades\DB;
$conn = 'mysql_aplikasi'; // WAJIB mysql_aplikasi agar sesuai dengan web frontend

// --- AUTO MIGRATE TABLE ---
DB::connection($conn)->statement("
    CREATE TABLE IF NOT EXISTS `anomaly_repairs` (
        `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `bill_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
        `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        `snapshot` text COLLATE utf8mb4_unicode_ci NOT NULL,
        `created_at` timestamp NULL DEFAULT NULL,
        `reverted_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

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

// --- AJAX HANDLER FOR Update Koreksi ---
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'fix_repair') {
    header('Content-Type: application/json');
    $bill_id = $_POST['bill_id'] ?? '';
    $cat = $_POST['category'] ?? '';

    if (!$bill_id || !$cat) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }

    DB::connection($conn)->beginTransaction();
    try {
        $snapshot = [];
        if ($cat === 'cat2') {
            $old = DB::connection($conn)->table('bills')->where('id', $bill_id)->first();
            DB::connection($conn)->table('bills')->where('id', $bill_id)->update(['status' => 'PAID', 'paid_amount' => DB::raw('amount'), 'updated_at' => now()]);
            $snapshot = ['old_status' => 'UNPAID', 'old_paid' => $old->paid_amount];
            $msg = 'Sukses: Status tagihan berhasil divalidasi menjadi PAID.';
        } 
        elseif ($cat === 'cat3') {
            $old = DB::connection($conn)->table('bills')->where('id', $bill_id)->first();
            $act = DB::connection($conn)->table('transaction_details')
                ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
                ->where('transaction_details.bill_id', $bill_id)
                ->where('transactions.status', 'PAID')
                ->whereNull('transaction_details.deleted_at')
                ->whereNull('transactions.deleted_at')
                ->sum('transactions.pay_amount');
            DB::connection($conn)->table('bills')->where('id', $bill_id)->update(['status' => 'UNPAID', 'paid_amount' => $act, 'updated_at' => now()]);
            $snapshot = ['old_status' => 'PAID', 'old_paid' => $old->paid_amount];
            $msg = 'Sukses: Status tagihan dikembalikan ke UNPAID.';
        }
        elseif ($cat === 'cat5') {
            $txs = DB::connection($conn)->select("
                SELECT t.id as tx_id, td.id as td_id
                FROM transactions t
                JOIN transaction_details td ON td.transaction_id = t.id
                WHERE td.bill_id = ? AND t.status = 'PAID' AND t.deleted_at IS NULL AND td.deleted_at IS NULL
                ORDER BY t.created_at ASC
            ", [$bill_id]);
            
            if (count($txs) > 1) {
                $deleted = 0;
                $del_txs = [];
                $del_tds = [];
                for ($i = 1; $i < count($txs); $i++) {
                    DB::connection($conn)->table('transactions')->where('id', $txs[$i]->tx_id)->update(['deleted_at' => now(), 'status' => 'FAILED_DUPLICATE', 'updated_at' => now()]);
                    DB::connection($conn)->table('transaction_details')->where('id', $txs[$i]->td_id)->update(['deleted_at' => now(), 'updated_at' => now()]);
                    $del_txs[] = $txs[$i]->tx_id;
                    $del_tds[] = $txs[$i]->td_id;
                    $deleted++;
                }
                $snapshot = ['deleted_txs' => $del_txs, 'deleted_tds' => $del_tds];
                $msg = "Sukses: Divalidasi dengan menghapus $deleted transaksi duplikat.";
            } else {
                $msg = 'Info: Tidak ditemukan duplikat aktif.';
            }
        } else {
            throw new Exception("Unknown category");
        }
        
        if(!empty($snapshot)) {
            DB::connection($conn)->table('anomaly_repairs')->insert([
                'bill_id' => $bill_id,
                'category' => $cat,
                'snapshot' => json_encode($snapshot),
                'created_at' => now()
            ]);
        }
        
        DB::connection($conn)->commit();
        echo json_encode(['success' => true, 'message' => $msg]);
    } catch (\Exception $e) {
        DB::connection($conn)->rollBack();
        echo json_encode(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
    }
    exit;
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'fix_repair_multi') {
    header('Content-Type: application/json');
    $items = json_decode($_POST['items'] ?? '[]', true);
    if (empty($items)) { echo json_encode(['success' => false, 'message' => 'No items']); exit; }

    DB::connection($conn)->beginTransaction();
    try {
        $count = 0;
        foreach($items as $it) {
            $bill_id = $it['bill_id'];
            $cat = $it['cat'];
            $snapshot = [];
            
            if ($cat === 'cat2') {
                $old = DB::connection($conn)->table('bills')->where('id', $bill_id)->first();
                DB::connection($conn)->table('bills')->where('id', $bill_id)->update(['status' => 'PAID', 'paid_amount' => DB::raw('amount'), 'updated_at' => now()]);
                $snapshot = ['old_status' => 'UNPAID', 'old_paid' => $old->paid_amount];
            } elseif ($cat === 'cat3') {
                $old = DB::connection($conn)->table('bills')->where('id', $bill_id)->first();
                $act = DB::connection($conn)->table('transaction_details')
                    ->join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
                    ->where('transaction_details.bill_id', $bill_id)
                    ->where('transactions.status', 'PAID')
                    ->whereNull('transaction_details.deleted_at')
                    ->whereNull('transactions.deleted_at')
                    ->sum('transactions.pay_amount');
                DB::connection($conn)->table('bills')->where('id', $bill_id)->update(['status' => 'UNPAID', 'paid_amount' => $act, 'updated_at' => now()]);
                $snapshot = ['old_status' => 'PAID', 'old_paid' => $old->paid_amount];
            } elseif ($cat === 'cat5') {
                $txs = DB::connection($conn)->select("
                    SELECT t.id as tx_id, td.id as td_id
                    FROM transactions t JOIN transaction_details td ON td.transaction_id = t.id
                    WHERE td.bill_id = ? AND t.status = 'PAID' AND t.deleted_at IS NULL AND td.deleted_at IS NULL ORDER BY t.created_at ASC
                ", [$bill_id]);
                if (count($txs) > 1) {
                    $del_txs = []; $del_tds = [];
                    for ($i = 1; $i < count($txs); $i++) {
                        DB::connection($conn)->table('transactions')->where('id', $txs[$i]->tx_id)->update(['deleted_at' => now(), 'status' => 'FAILED_DUPLICATE', 'updated_at' => now()]);
                        DB::connection($conn)->table('transaction_details')->where('id', $txs[$i]->td_id)->update(['deleted_at' => now(), 'updated_at' => now()]);
                        $del_txs[] = $txs[$i]->tx_id; $del_tds[] = $txs[$i]->td_id;
                    }
                    $snapshot = ['deleted_txs' => $del_txs, 'deleted_tds' => $del_tds];
                }
            }
            if(!empty($snapshot)) {
                DB::connection($conn)->table('anomaly_repairs')->insert([
                    'bill_id' => $bill_id, 'category' => $cat, 'snapshot' => json_encode($snapshot), 'created_at' => now()
                ]);
                $count++;
            }
        }
        DB::connection($conn)->commit();
        echo json_encode(['success' => true, 'message' => "Sukses: $count tagihan berhasil diupdate secara kolektif!"]);
    } catch (\Exception $e) {
        DB::connection($conn)->rollBack();
        echo json_encode(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()]);
    }
    exit;
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'revert_repair_multi') {
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

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'revert_repair') {
    header('Content-Type: application/json');
    $repair_id = $_POST['repair_id'] ?? '';
    if (!$repair_id) { echo json_encode(['success' => false, 'message' => 'Invalid ID']); exit; }
    
    DB::connection($conn)->beginTransaction();
    try {
        $repair = DB::connection($conn)->table('anomaly_repairs')->where('id', $repair_id)->first();
        if (!$repair || $repair->reverted_at) throw new Exception("Data tidak valid / sudah dibatalkan.");
        
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
        DB::connection($conn)->commit();
        echo json_encode(['success' => true, 'message' => 'Berhasil membatalkan (rollback) perbaikan.']);
    } catch (\Exception $e) {
        DB::connection($conn)->rollBack();
        echo json_encode(['success' => false, 'message' => 'Gagal Rollback: ' . $e->getMessage()]);
    }
    exit;
}

// --- HELPER GROUPING ---
function groupDataBySantriAndTagihan($data) {
    $res = [];
    foreach($data as $r) {
        $k = $r->santri . '|||' . $r->kelas;
        if(!isset($res[$k])) {
            $res[$k] = [
                'santri' => $r->santri, 
                'kelas' => $r->kelas, 
                'tagihan_groups' => []
            ];
        }
        $tagihan_name = $r->tagihan;
        if(!isset($res[$k]['tagihan_groups'][$tagihan_name])) {
            $res[$k]['tagihan_groups'][$tagihan_name] = [];
        }
        $res[$k]['tagihan_groups'][$tagihan_name][] = $r;
    }
    
    // Urutkan tiap grup tagihan berdasarkan Tahun lalu Bulan
    foreach($res as &$studentGroup) {
        foreach($studentGroup['tagihan_groups'] as $tName => &$items) {
            usort($items, function($a, $b) {
                $yA = isset($a->year) ? (int)$a->year : 0;
                $yB = isset($b->year) ? (int)$b->year : 0;
                if ($yA !== $yB) return $yA <=> $yB;
                
                $mA = isset($a->month) ? (int)$a->month : 0;
                $mB = isset($b->month) ? (int)$b->month : 0;
                return $mA <=> $mB;
            });
        }
    }
    unset($studentGroup, $items);

    return array_values($res);
}

// --- QUERIES ---

// 1. Orphan Paid (Tidak ada transaksi, jadi tidak ada info petugas/tgl)
$q1 = DB::connection($conn)->select("
    SELECT b.id AS bill_id, s.name AS santri, COALESCE(c.name,'-') AS kelas, bt.name AS tagihan, b.month, b.year, b.amount 
    FROM bills b 
    JOIN students s ON s.id=b.student_id 
    LEFT JOIN classrooms c ON c.id=s.classroom_id 
    JOIN bill_types bt ON bt.id=b.bill_type_id
    JOIN academic_years ay ON ay.id=b.academic_year_id
    WHERE b.deleted_at IS NULL AND b.status='PAID' AND ay.name = '2026/2027'
    AND NOT EXISTS (
        SELECT 1 FROM transaction_details td JOIN transactions t ON t.id=td.transaction_id 
        WHERE td.bill_id=b.id AND td.deleted_at IS NULL AND t.deleted_at IS NULL AND t.status='PAID'
    )
");

// 2. Missed Update (Ada transaksi, ambil info evidence)
$q2 = DB::connection($conn)->select("
    SELECT b.id AS bill_id, s.name AS santri, COALESCE(c.name,'-') AS kelas, bt.name AS tagihan, b.month, b.year, b.amount, t.paid_at, a.name AS admin_petugas, il_admin.name AS import_admin, t.import_log_id, t.admin_id, t.user_id 
    FROM bills b 
    JOIN students s ON s.id=b.student_id 
    LEFT JOIN classrooms c ON c.id=s.classroom_id 
    JOIN bill_types bt ON bt.id=b.bill_type_id
    JOIN academic_years ay ON ay.id=b.academic_year_id
    JOIN transaction_details td ON td.bill_id=b.id
    JOIN transactions t ON t.id=td.transaction_id
    LEFT JOIN admins a ON a.id = t.admin_id
    LEFT JOIN import_logs il ON il.id = t.import_log_id
    LEFT JOIN admins il_admin ON il_admin.id = il.admin_id
    WHERE b.deleted_at IS NULL AND b.status='UNPAID' AND ay.name = '2026/2027'
    AND td.deleted_at IS NULL AND t.deleted_at IS NULL AND t.status='PAID'
    GROUP BY b.id, s.name, c.name, bt.name, b.month, b.year, b.amount, t.paid_at, a.name, il_admin.name, t.import_log_id, t.admin_id, t.user_id
");

// 3. Partial Paid (Ada transaksi, ambil info evidence)
$q3 = DB::connection($conn)->select("
    SELECT b.id AS bill_id, s.name AS santri, COALESCE(c.name,'-') AS kelas, bt.name AS tagihan, b.month, b.year, b.amount AS tagihan_rp, t.pay_amount AS dibayar_rp, (b.amount - t.pay_amount) AS sisa_rp, t.paid_at, a.name AS admin_petugas, il_admin.name AS import_admin, t.import_log_id, t.admin_id, t.user_id
    FROM bills b 
    JOIN students s ON s.id=b.student_id 
    LEFT JOIN classrooms c ON c.id=s.classroom_id 
    JOIN bill_types bt ON bt.id=b.bill_type_id
    JOIN transaction_details td ON td.bill_id=b.id 
    JOIN transactions t ON t.id=td.transaction_id
    LEFT JOIN admins a ON a.id = t.admin_id
    LEFT JOIN import_logs il ON il.id = t.import_log_id
    LEFT JOIN admins il_admin ON il_admin.id = il.admin_id
    JOIN academic_years ay ON ay.id=b.academic_year_id
    WHERE b.deleted_at IS NULL AND b.status='PAID' AND t.status='PAID' AND t.deleted_at IS NULL AND td.deleted_at IS NULL
    AND ay.name = '2026/2027'
    AND t.pay_amount < b.amount
    AND (SELECT COUNT(*) FROM transaction_details td2 WHERE td2.transaction_id=t.id AND td2.deleted_at IS NULL) = 1
");

// 5. Duplikat TX (Ada multiple transaksi per bill, ambil semua evidence untuk dirender berdampingan)
$q6 = DB::connection($conn)->select("
    SELECT b.id AS bill_id, s.name AS santri, COALESCE(c.name,'-') AS kelas, bt.name AS tagihan, b.month, b.year, b.amount, t.paid_at, a.name AS admin_petugas, il_admin.name AS import_admin, t.import_log_id, t.admin_id, t.user_id
    FROM bills b
    JOIN transaction_details td ON td.bill_id = b.id
    JOIN transactions t ON t.id = td.transaction_id
    LEFT JOIN admins a ON a.id = t.admin_id
    LEFT JOIN import_logs il ON il.id = t.import_log_id
    LEFT JOIN admins il_admin ON il_admin.id = il.admin_id
    JOIN students s ON s.id = b.student_id
    LEFT JOIN classrooms c ON c.id = s.classroom_id
    JOIN bill_types bt ON bt.id = b.bill_type_id
    JOIN academic_years ay ON ay.id=b.academic_year_id
    WHERE b.deleted_at IS NULL AND td.deleted_at IS NULL AND t.deleted_at IS NULL AND t.status = 'PAID'
    AND ay.name = '2026/2027'
    AND b.id IN (
        SELECT b2.id FROM bills b2
        JOIN transaction_details td2 ON td2.bill_id = b2.id
        JOIN transactions t2 ON t2.id = td2.transaction_id
        WHERE b2.deleted_at IS NULL AND td2.deleted_at IS NULL AND t2.deleted_at IS NULL AND t2.status = 'PAID'
        GROUP BY b2.id HAVING COUNT(t2.id) > 1
    )
    ORDER BY s.name, bt.name, b.month, b.year
");

$g1 = groupDataBySantriAndTagihan($q1);
$g2 = groupDataBySantriAndTagihan($q2);
$g3 = groupDataBySantriAndTagihan($q3);
$g6 = groupDataBySantriAndTagihan($q6);
$indo_months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agt',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];

// History
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
$g_history = groupDataBySantriAndTagihan($history_repairs);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Anomali VPS 2026/2027</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .text-h1 { font-size: 22px; font-weight: 700; color: #0f172a; }
        .text-h2 { font-size: 16px; font-weight: 600; color: #1e293b; }
        .text-amount { font-size: 14px; font-weight: 700; color: #10b981; }
        .text-amount-error { font-size: 14px; font-weight: 700; color: #dc2626; }
        .text-label { font-size: 11px; font-weight: 700; text-transform: capitalize; letter-spacing: 0.05em; color: #94a3b8; }
        .text-body { font-size: 14px; font-weight: 500; color: #475569; }
    </style>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    body, div, span, h1, h2, h3, h4, h5, h6, p, a, button, input { font-family: 'Inter', sans-serif !important; }
</style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 pb-20">
    
    <!-- Sticky Header -->
    <div class="sticky top-0 z-50 backdrop-blur-md bg-white/80 border-b border-slate-200 px-5 py-4 shadow-sm mb-6">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-h1">Audit Data Anomali VPS</h1>
                    <span class="bg-blue-100 text-blue-700 text-[12px] font-bold px-3 py-1 rounded-full capitalize tracking-wider">T.A. 2026/2027</span>
                </div>
                <p class="text-sm text-slate-500 mt-1">Menggunakan koneksi live <strong class="text-slate-700">mysql_aplikasi</strong>. Menampilkan evidence metode entry.</p>
            </div>
            <a href="?export=csv" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-5 py-2.5 rounded-[24px] text-[14px] font-medium shadow-sm transition-all flex items-center gap-2">
                Download CSV
            </a>
        </div>
    </div>

        <div class="max-w-6xl mx-auto px-5 mb-6 mt-6">
        <div class="flex gap-4 border-b border-slate-200">
            <button onclick="switchTab('aktif')" id="tab-aktif" class="px-6 py-3 text-sm font-bold text-blue-600 border-b-2 border-blue-600">Anomaly Aktif</button>
            <button onclick="switchTab('riwayat')" id="tab-riwayat" class="px-6 py-3 text-sm font-medium text-slate-500 hover:text-slate-700">Riwayat Perbaikan</button>
        </div>
    </div>
<div id="content-aktif" class="max-w-6xl mx-auto px-5 space-y-6">
    <div class="relative mb-2">
        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
        <input type="text" id="search-aktif" onkeyup="filterStudents('aktif')" placeholder="Cari nama siswa atau kelas..." class="bg-white border border-slate-200 text-slate-800 text-sm font-medium rounded-xl focus:ring-blue-500 focus:border-blue-500 block w-full pl-12 p-3.5 shadow-[0_2px_10px_rgb(0,0,0,0.02)] placeholder-slate-400">
    </div>

        <!-- CARD 2: Missed Update -->
        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h2 class="text-h2 text-amber-600 flex items-center gap-2">
                    <span class="bg-amber-100 p-1.5 rounded-full"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                    Kategori 2: Missed Update (Sudah Bayar tapi Status Belum Terbayar)
                </h2>
                <button id="btn-fix-all-cat2" onclick="startMassiveFixCat2()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold px-5 py-2.5 rounded-xl transition-colors shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Perbaiki Semua Cat 2
                </button>
            </div>
            <div class="bg-amber-50 border-y border-amber-100 px-6 py-4 text-sm">
                <p><strong class="text-amber-800">Disebabkan oleh:</strong> Santri berhasil membayar (riwayat transaksi sukses ada), tetapi callback sistem gagal memperbarui status tagihan menjadi PAID.</p>
                <p class="mt-1"><strong class="text-amber-800">Bukti Catatan:</strong> Transaksi berlabel <span class="bg-emerald-100 text-emerald-700 px-1 rounded text-xs font-bold">Legal</span> (Aksi User / Import) menandakan transaksi tersebut sah, sehingga anomali murni terletak pada <strong class="underline">status tagihannya yang nyangkut</strong>, bukan transaksinya.</p>
            </div>

            <div>
                <?php if(count($g2) > 0):?>
                    <?php $anomaly_category = "cat2"; $no = 1; foreach($g2 as $g):?>
                    <div class="student-wrapper-aktif border-b border-slate-100 p-5 hover:bg-slate-50/50" data-search="<?= strtolower(htmlspecialchars($g['santri'].' '.$g['kelas'])) ?>">
                        <?php $stu_hash = md5($anomaly_category.$g['santri'].$g['kelas']); ?>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-slate-800 text-lg">
                                <?= $no++ ?>. <?= htmlspecialchars(ucwords(strtolower($g['santri']))) ?> <span class="text-sm text-slate-500 font-normal ml-2">(Kelas: <?= htmlspecialchars(ucwords(strtolower($g['kelas']))) ?>)</span>
                            </h3>
                            <button onclick="fixMultiAnomaly('<?= $stu_hash ?>', this)" class="bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold px-4 py-2 capitalize tracking-widest rounded-lg transition-colors shadow-sm hidden btn-bulk-<?= $stu_hash ?>">
                                Update Koreksi Terpilih (<span class="count-<?= $stu_hash ?>">0</span>)
                            </button>
                        </div>
                        <div class="space-y-5 pl-4 border-l-2 border-slate-100 ml-2">
                            <?php foreach($g['tagihan_groups'] as $tagihan_name => $items):?>
                            <div>
                                <?php $tag_hash = md5($anomaly_category.$g['santri'].$tagihan_name); ?>
                                <div class="flex items-center gap-2 mb-3 mt-2">
                                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                    <h4 class="text-sm font-bold text-slate-600 capitalize tracking-wider">
                                        <?= htmlspecialchars(ucwords(strtolower($tagihan_name))) ?>
                                    </h4>
                                    <label class="ml-auto text-[10px] font-bold text-slate-500 font-bold text-[11px] flex items-center gap-1.5 cursor-pointer hover:text-blue-600">
                                        <input type="checkbox" onchange="toggleSelectAll('<?= $tag_hash ?>', this.checked, '<?= $stu_hash ?>')" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        Pilih Semua
                                    </label>
                                </div>
                                <!-- Grid Cards -->
                                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                    <?php foreach($items as $item): 
                                        $is_import = !empty($item->import_log_id);
                                        $real_admin = !empty($item->admin_petugas) ? ucwords(strtolower($item->admin_petugas)) : null;
                                        $import_admin = !empty($item->import_admin) ? ucwords(strtolower($item->import_admin)) : null;
                                        
                                        if ($is_import) {
                                            $entry_method = 'Import Data (Legal)';
                                            $entry_color = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                            $petugas = $import_admin ?? $real_admin ?? 'Sistem / Admin';
                                        } elseif ($real_admin) {
                                            $entry_method = 'Aksi User (Legal)';
                                            $entry_color = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                            $petugas = $real_admin;
                                        } else {
                                            $entry_method = 'Approval Aplikasi';
                                            $entry_color = 'bg-blue-100 text-blue-700 border border-blue-200';
                                            $petugas = 'Sistem / Admin';
                                        }
                                        $tgl = '-';
if (!empty($item->paid_at)) {
    $ts = strtotime($item->paid_at);
    $tgl = date('d', $ts) . ' ' . $indo_months[(int)date('n', $ts)] . ' ' . date('Y H:i', $ts);
}
?>
                                    <div class="border border-slate-200 rounded-xl p-3 bg-white shadow-[0_2px_8px_rgb(0,0,0,0.04)] flex flex-col justify-between relative">
                                        <input type="checkbox" value="<?= $item->bill_id ?>" data-cat="<?= $anomaly_category ?>" class="chk-<?= $tag_hash ?> chk-group-<?= $stu_hash ?> w-4 h-4 text-blue-600 rounded border-slate-300 absolute top-3 right-3 cursor-pointer shadow-sm" onchange="updateBulkCount('<?= $stu_hash ?>')">
                                        <div>
                                            <div class="text-[11px] font-bold text-slate-500 capitalize tracking-widest mb-1">Periode</div>
                                            <div class="text-base font-extrabold text-slate-800 mb-2"><?= $indo_months[(int)$item->month] ?>-<?= $item->year ?></div>
                                            <div class="text-[11px] font-bold text-slate-400 capitalize tracking-widest mb-0.5">Nominal:</div>
                                            <div class="text-red-600 text-lg font-extrabold">Rp <?= number_format($item->amount,0,',','.')?></div>
                                        </div>
                                        <div class="mt-3 pt-3 border-t border-slate-100 flex flex-col gap-1.5">
                                            <div class="text-[10px] text-slate-500 font-bold text-[11px] flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                <?= $tgl?>
                                            </div>
                                            <div class="text-[11px] font-bold bg-white border border-slate-200 px-2.5 py-1 rounded-md text-blue-600 truncate flex items-center gap-1.5 shadow-sm" title="<?= htmlspecialchars($petugas)?>">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                <?= htmlspecialchars($petugas)?>
                                            </div>
                                            <div class="text-[9px] <?= $entry_color?> px-1.5 py-1 rounded font-semibold text-center mt-1 capitalize tracking-wide">
                                                <?= $entry_method?>
                                            </div>
                                            <button onclick="fixAnomaly('<?= $item->bill_id?>', 'cat2', this)" class="mt-2 w-full bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold py-2 rounded transition-colors shadow-sm flex items-center justify-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>Update Koreksi</button>
                                        </div>
                                    </div>
                                    <?php endforeach;?>
                                </div>
                            </div>
                            <?php endforeach;?>
                        </div>
                    </div>
                    <?php endforeach;?>
                <?php else:?>
                    <div class="p-8 text-center text-slate-500 italic">Tidak ada data.</div>
                <?php endif;?>
            </div>
        </div>

        <!-- CARD 3: Partial Paid -->
        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h2 class="text-h2 text-red-600 flex items-center gap-2">
                    <span class="bg-red-100 p-1.5 rounded-full"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></span>
                    Kategori 3: Partial Salah PAID (Bayar Nyicil tapi Lunas)
                </h2>
            </div>
            <div class="bg-rose-50 border-y border-rose-100 px-6 py-4 text-sm">
                <p><strong class="text-rose-800">Disebabkan oleh:</strong> Santri menginput cicilan, tapi sistem memukul rata statusnya menjadi LUNAS.</p>
                <p class="mt-1"><strong class="text-rose-800">Bukti Catatan:</strong> Jika metode entry adalah <span class="bg-emerald-100 text-emerald-700 px-1 rounded text-xs font-bold">Legal</span> (Aksi User / Import Data), maka uang cicilan tersebut <strong class="underline">SAH</strong> masuk. Anomali murni hanya pada status tagihan yang seharusnya masih berstatus UNPAID dan menyisakan sisa.</p>
            </div>
            <div>
                <?php if(count($g3) > 0):?>
                    <?php $anomaly_category = "cat3"; $no = 1; foreach($g3 as $g):?>
                    <div class="student-wrapper-aktif border-b border-slate-100 p-5 hover:bg-slate-50/50" data-search="<?= strtolower(htmlspecialchars($g['santri'].' '.$g['kelas'])) ?>">
                        <?php $stu_hash = md5($anomaly_category.$g['santri'].$g['kelas']); ?>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-slate-800 text-lg">
                                <?= $no++ ?>. <?= htmlspecialchars(ucwords(strtolower($g['santri']))) ?> <span class="text-sm text-slate-500 font-normal ml-2">(Kelas: <?= htmlspecialchars(ucwords(strtolower($g['kelas']))) ?>)</span>
                            </h3>
                            <button onclick="fixMultiAnomaly('<?= $stu_hash ?>', this)" class="bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold px-4 py-2 capitalize tracking-widest rounded-lg transition-colors shadow-sm hidden btn-bulk-<?= $stu_hash ?>">
                                Update Koreksi Terpilih (<span class="count-<?= $stu_hash ?>">0</span>)
                            </button>
                        </div>
                        <div class="space-y-5 pl-4 border-l-2 border-slate-100 ml-2">
                            <?php foreach($g['tagihan_groups'] as $tagihan_name => $items):?>
                            <div>
                                <?php $tag_hash = md5($anomaly_category.$g['santri'].$tagihan_name); ?>
                                <div class="flex items-center gap-2 mb-3 mt-2">
                                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                    <h4 class="text-sm font-bold text-slate-600 capitalize tracking-wider">
                                        <?= htmlspecialchars(ucwords(strtolower($tagihan_name))) ?>
                                    </h4>
                                    <label class="ml-auto text-[10px] font-bold text-slate-500 font-bold text-[11px] flex items-center gap-1.5 cursor-pointer hover:text-blue-600">
                                        <input type="checkbox" onchange="toggleSelectAll('<?= $tag_hash ?>', this.checked, '<?= $stu_hash ?>')" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        Pilih Semua
                                    </label>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                    <?php foreach($items as $item): 
                                        $is_import = !empty($item->import_log_id);
                                        $real_admin = !empty($item->admin_petugas) ? ucwords(strtolower($item->admin_petugas)) : null;
                                        $import_admin = !empty($item->import_admin) ? ucwords(strtolower($item->import_admin)) : null;
                                        
                                        if ($is_import) {
                                            $entry_method = 'Import Data (Legal)';
                                            $entry_color = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                            $petugas = $import_admin ?? $real_admin ?? 'Sistem / Admin';
                                        } elseif ($real_admin) {
                                            $entry_method = 'Aksi User (Legal)';
                                            $entry_color = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                            $petugas = $real_admin;
                                        } else {
                                            $entry_method = 'Approval Aplikasi';
                                            $entry_color = 'bg-blue-100 text-blue-700 border border-blue-200';
                                            $petugas = 'Sistem / Admin';
                                        }
                                        $tgl = '-';
if (!empty($item->paid_at)) {
    $ts = strtotime($item->paid_at);
    $tgl = date('d', $ts) . ' ' . $indo_months[(int)date('n', $ts)] . ' ' . date('Y H:i', $ts);
}
?>
                                    <div class="border border-slate-200 rounded-xl p-4 bg-white shadow-[0_2px_8px_rgb(0,0,0,0.04)] flex flex-col justify-between">
                                        <div>
                                            <div class="flex justify-between items-center mb-2">
                                                <div class="text-[11px] font-bold text-slate-500 capitalize tracking-widest">Periode</div>
                                                <div class="text-sm font-extrabold text-slate-800 bg-slate-100 px-2 py-0.5 rounded"><?= $indo_months[(int)$item->month] ?>-<?= $item->year ?></div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 text-xs mb-3 bg-slate-50 p-2 rounded-lg border border-slate-100">
                                                <div class="text-slate-500">Tagihan:</div><div class="text-right font-medium text-slate-700">Rp <?= number_format($item->tagihan_rp,0,',','.')?></div>
                                                <div class="text-slate-500">Bayar:</div><div class="text-right font-bold text-emerald-600">Rp <?= number_format($item->dibayar_rp,0,',','.')?></div>
                                                <div class="text-slate-500 pt-1 border-t border-slate-200">Sisa:</div><div class="text-right font-bold text-rose-600 pt-1 border-t border-slate-200">Rp <?= number_format($item->sisa_rp,0,',','.')?></div>
                                            </div>
                                        </div>
                                        <div class="mt-1 flex flex-col gap-1.5">
                                            <div class="flex justify-between items-center text-[10px]">
                                                <div class="text-slate-500 font-bold text-[11px] flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg><?= $tgl?></div>
                                                <div class="text-blue-600 font-bold text-[11px] flex items-center gap-1" title="<?= htmlspecialchars($petugas)?>"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg><?= htmlspecialchars(substr($petugas,0,10))?>..</div>
                                            </div>
                                            <div class="text-[10px] <?= $entry_color?> px-1.5 py-1 rounded font-semibold text-center mt-1 capitalize tracking-wide">
                                                <?= $entry_method?>
                                            </div>
                                            <button onclick="fixAnomaly('<?= $item->bill_id?>', 'cat3', this)" class="mt-2 w-full bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold py-2 rounded transition-colors shadow-sm flex items-center justify-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>Update Koreksi</button>
                                        </div>
                                    </div>
                                    <?php endforeach;?>
                                </div>
                            </div>
                            <?php endforeach;?>
                        </div>
                    </div>
                    <?php endforeach;?>
                <?php else:?>
                    <div class="p-8 text-center text-slate-500 italic">Tidak ada data.</div>
                <?php endif;?>
            </div>
        </div>
        
        <!-- CARD 5: Duplikat TX -->
        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden mb-10">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h2 class="text-h2 text-amber-600 flex items-center gap-2">
                    <span class="bg-amber-100 p-1.5 rounded-full"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                    Kategori 5: Duplikat Transaksi Lunas
                </h2>
            </div>
            <div class="bg-amber-50 border-y border-amber-100 px-6 py-4 text-sm">
                <p><strong class="text-amber-800">Disebabkan oleh:</strong> Terdapat lebih dari 1 riwayat transaksi lunas untuk 1 tagihan periode yang persis sama.</p>
                <p class="mt-1"><strong class="text-amber-800">Bukti Catatan:</strong> Kartu-kartu di bawah ini berdampingan untuk <strong class="underline">tagihan yang sama</strong>. Jika ada kartu berstatus <span class="bg-emerald-100 text-emerald-700 px-1 rounded text-xs font-bold">Legal</span> (Aksi User / Import), maka transaksi tersebut sah masuk. Anda bisa melakukan cross-check secara visual mengapa terjadi duplikasi (misal: satu dari gateway, satu lagi diinput user manual).</p>
            </div>
            <div>
                <?php if(count($g6) > 0):?>
                    <?php $anomaly_category = "cat5"; $no = 1; foreach($g6 as $g):?>
                    <div class="student-wrapper-aktif border-b border-slate-100 p-5 hover:bg-slate-50/50" data-search="<?= strtolower(htmlspecialchars($g['santri'].' '.$g['kelas'])) ?>">
                        <?php $stu_hash = md5($anomaly_category.$g['santri'].$g['kelas']); ?>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-slate-800 text-lg">
                                <?= $no++ ?>. <?= htmlspecialchars(ucwords(strtolower($g['santri']))) ?> <span class="text-sm text-slate-500 font-normal ml-2">(Kelas: <?= htmlspecialchars(ucwords(strtolower($g['kelas']))) ?>)</span>
                            </h3>
                            <button onclick="fixMultiAnomaly('<?= $stu_hash ?>', this)" class="bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold px-4 py-2 capitalize tracking-widest rounded-lg transition-colors shadow-sm hidden btn-bulk-<?= $stu_hash ?>">
                                Update Koreksi Terpilih (<span class="count-<?= $stu_hash ?>">0</span>)
                            </button>
                        </div>
                        <div class="space-y-5 pl-4 border-l-2 border-slate-100 ml-2">
                            <?php foreach($g['tagihan_groups'] as $tagihan_name => $items):?>
                            <div>
                                <?php $tag_hash = md5($anomaly_category.$g['santri'].$tagihan_name); ?>
                                <div class="flex items-center gap-2 mb-3 mt-2">
                                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                    <h4 class="text-sm font-bold text-slate-600 capitalize tracking-wider">
                                        <?= htmlspecialchars(ucwords(strtolower($tagihan_name))) ?>
                                    </h4>
                                    <label class="ml-auto text-[10px] font-bold text-slate-500 font-bold text-[11px] flex items-center gap-1.5 cursor-pointer hover:text-blue-600">
                                        <input type="checkbox" onchange="toggleSelectAll('<?= $tag_hash ?>', this.checked, '<?= $stu_hash ?>')" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        Pilih Semua
                                    </label>
                                </div>
                                <!-- Grid Cards (Group duplicate transactions for the same bill together visually) -->
                                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                    <?php foreach($items as $item): 
                                        $is_import = !empty($item->import_log_id);
                                        $real_admin = !empty($item->admin_petugas) ? ucwords(strtolower($item->admin_petugas)) : null;
                                        $import_admin = !empty($item->import_admin) ? ucwords(strtolower($item->import_admin)) : null;
                                        
                                        if ($is_import) {
                                            $entry_method = 'Import Data (Legal)';
                                            $entry_color = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                            $petugas = $import_admin ?? $real_admin ?? 'Sistem / Admin';
                                        } elseif ($real_admin) {
                                            $entry_method = 'Aksi User (Legal)';
                                            $entry_color = 'bg-emerald-100 text-emerald-700 border border-emerald-200';
                                            $petugas = $real_admin;
                                        } else {
                                            $entry_method = 'Approval Aplikasi';
                                            $entry_color = 'bg-blue-100 text-blue-700 border border-blue-200';
                                            $petugas = 'Sistem / Admin';
                                        }
                                        $tgl = '-';
if (!empty($item->paid_at)) {
    $ts = strtotime($item->paid_at);
    $tgl = date('d', $ts) . ' ' . $indo_months[(int)date('n', $ts)] . ' ' . date('Y H:i', $ts);
}
?>
                                    <div class="border border-red-200 rounded-xl p-3 bg-red-50/30 shadow-[0_2px_8px_rgb(0,0,0,0.04)] flex flex-col justify-between relative">
                                        <input type="checkbox" value="<?= $item->bill_id ?>" data-cat="<?= $anomaly_category ?>" class="chk-<?= $tag_hash ?> chk-group-<?= $stu_hash ?> w-4 h-4 text-blue-600 rounded border-slate-300 absolute top-3 right-3 cursor-pointer shadow-sm" onchange="updateBulkCount('<?= $stu_hash ?>')">
                                        <div>
                                            <div class="text-[11px] font-bold text-red-500 capitalize tracking-widest mb-1">Periode Duplikat</div>
                                            <div class="text-base font-extrabold text-slate-800 mb-2"><?= $indo_months[(int)$item->month] ?>-<?= $item->year ?></div>
                                            <div class="text-red-600 text-lg font-extrabold mb-2">Rp <?= number_format($item->amount,0,',','.')?></div>
                                        </div>
                                        <div class="mt-2 pt-2 border-t border-slate-200 flex flex-col gap-1.5">
                                            <div class="text-[10px] text-slate-600 flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                <?= $tgl?>
                                            </div>
                                            <div class="text-[11px] font-bold bg-white border border-slate-200 px-2.5 py-1 rounded-md text-blue-600 truncate flex items-center gap-1.5 shadow-sm" title="<?= htmlspecialchars($petugas)?>">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                <?= htmlspecialchars($petugas)?>
                                            </div>
                                            <div class="text-[9px] <?= $entry_color?> px-1.5 py-1 rounded font-semibold text-center mt-1 capitalize tracking-wide">
                                                <?= $entry_method?>
                                            </div>
                                            <button onclick="fixAnomaly('<?= $item->bill_id?>', 'cat5', this)" class="mt-2 w-full bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold py-2 rounded transition-colors shadow-sm flex items-center justify-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>Update Koreksi</button>
                                        </div>
                                    </div>
                                    <?php endforeach;?>
                                </div>
                            </div>
                            <?php endforeach;?>
                        </div>
                    </div>
                    <?php endforeach;?>
                <?php else:?>
                    <div class="p-8 text-center text-slate-500 italic">Tidak ada data.</div>
                <?php endif;?>
            </div>
        </div>

        </div> <!-- end content-aktif -->

    <div id="content-riwayat" class="max-w-6xl mx-auto px-5 space-y-6 hidden">
        <div class="bg-white rounded-[24px] shadow-[0_8px_30px_rgb(0,0,0,0.04)] overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h2 class="text-h2 text-emerald-600 flex items-center gap-2">
                    <span class="bg-emerald-100 p-1.5 rounded-full"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></span>
                    Riwayat Perbaikan Valid
                </h2>
            </div>
            <div>
                <?php if(count($g_history) > 0): ?>
                    <?php $no = 1; foreach($g_history as $g):?>
                    <div class="student-wrapper-riwayat border-b border-slate-100 p-5 hover:bg-slate-50/50" data-search="<?= strtolower(htmlspecialchars($g['santri'].' '.$g['kelas'])) ?>">
                        <?php $stu_hash = md5('hist'.$g['santri'].$g['kelas']); ?>
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-slate-800 text-lg capitalize">
                                <?= $no++ ?>. <?= htmlspecialchars(ucwords(strtolower($g['santri']))) ?> <span class="text-sm text-slate-500 font-normal ml-2 capitalize">(Kelas: <?= htmlspecialchars(ucwords(strtolower($g['kelas']))) ?>)</span>
                            </h3>
                            <button onclick="revertMultiRepair('<?= $stu_hash ?>', this)" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-[11px] font-bold px-4 py-2 tracking-widest rounded-lg transition-colors shadow-sm hidden btn-bulk-<?= $stu_hash ?>">
                                Batalkan Terpilih (<span class="count-<?= $stu_hash ?>">0</span>)
                            </button>
                        </div>
                        <div class="space-y-5 pl-4 border-l-2 border-slate-100 ml-2">
                            <?php foreach($g['tagihan_groups'] as $tagihan_name => $items):?>
                            <div>
                                <?php $tag_hash = md5('hist'.$g['santri'].$tagihan_name); ?>
                                <div class="flex items-center gap-2 mb-3 mt-2">
                                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                                    <h4 class="text-sm font-bold text-slate-600 capitalize tracking-wider">
                                        <?= htmlspecialchars(ucwords(strtolower($tagihan_name))) ?>
                                    </h4>
                                    <label class="ml-auto text-[11px] font-bold text-slate-500 flex items-center gap-1.5 cursor-pointer hover:text-blue-600">
                                        <input type="checkbox" onchange="toggleSelectAll('<?= $tag_hash ?>', this.checked, '<?= $stu_hash ?>')" class="w-3.5 h-3.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        Pilih Semua
                                    </label>
                                </div>
                                <!-- Grid Cards -->
                                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                    <?php foreach($items as $item): ?>
                                    <div class="border border-slate-200 rounded-xl p-4 bg-white shadow-[0_2px_8px_rgb(0,0,0,0.04)] flex flex-col justify-between relative">
                                        <input type="checkbox" value="<?= $item->repair_id ?>" class="chk-<?= $tag_hash ?> chk-group-<?= $stu_hash ?> w-4 h-4 text-blue-600 rounded border-slate-300 absolute top-3 right-3 cursor-pointer shadow-sm" onchange="updateBulkCount('<?= $stu_hash ?>')">
                                        <div>
                                            <div class="text-[11px] font-bold text-slate-500 capitalize tracking-widest mb-1">Periode</div>
                                            <div class="text-sm font-bold text-slate-800 mb-2 capitalize"><?= $indo_months[(int)$item->month] ?> <?= $item->year ?></div>
                                            <div class="text-[11px] font-bold text-slate-400 capitalize tracking-widest mb-0.5">Nominal</div>
                                            <div class="text-slate-700 text-sm font-bold">Rp <?= number_format($item->amount,0,',','.')?></div>
                                        </div>
                                        <div class="mt-3 pt-3 border-t border-slate-100 flex flex-col gap-1.5">
                                            <div class="text-[10px] text-slate-400">
                                                Anomali: <span class="font-bold text-slate-600 capitalize"><?= htmlspecialchars(ucwords(strtolower($item->category))) ?></span><br>
                                                Waktu: <?= date('d/m/Y H:i', strtotime($item->repair_time)) ?>
                                            </div>
                                            <button onclick="revertRepair('<?= $item->repair_id ?>', this)" class="mt-2 w-full bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-[11px] font-bold py-2 rounded transition-colors shadow-sm flex items-center justify-center gap-1 capitalize">Batalkan</button>
                                        </div>
                                    </div>
                                    <?php endforeach;?>
                                </div>
                            </div>
                            <?php endforeach;?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-8 text-center text-slate-500 italic">Belum ada riwayat perbaikan.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<script>
async function startMassiveFixCat2() {
    const btn = document.getElementById('btn-fix-all-cat2');
    
    // Step 1: Pre-count
    btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Menghitung...';
    btn.disabled = true;
    
    try {
        const countData = new FormData();
        countData.append('action', 'count_cat2');
        const countRes = await fetch('audit_vps.php', { method: 'POST', body: countData });
        const countJson = await countRes.json();
        
        if (!countJson.success || countJson.total === 0) {
            alert('Tidak ada anomali CAT2 yang perlu diperbaiki.');
            btn.innerHTML = 'Perbaiki Semua Cat 2';
            btn.disabled = false;
            return;
        }
        
        // Step 2: Confirmation
        const total = countJson.total;
        if (!confirm(
            'MASSIVE BULK FIX — Kategori 2\n\n' +
            'Total tagihan anomali: ' + total + ' bills\n' +
            'Proses akan berjalan dalam batch @50 tagihan.\n\n' +
            'Setiap batch memiliki snapshot rollback.\n' +
            'Lanjutkan?'
        )) {
            btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Perbaiki Semua Cat 2';
            btn.disabled = false;
            return;
        }
        
        // Step 3: Show progress bar
        const progressEl = document.getElementById('massive-fix-progress');
        const progressBar = document.getElementById('progress-bar');
        const progressLabel = document.getElementById('progress-label');
        const progressPct = document.getElementById('progress-pct');
        const progressDetail = document.getElementById('progress-detail');
        progressEl.classList.remove('hidden');
        btn.classList.add('hidden');
        
        // Step 4: Chunked processing loop
        let totalProcessed = 0;
        let batchNum = 0;
        let remaining = total;
        
        while (remaining > 0) {
            batchNum++;
            progressLabel.textContent = 'Batch ' + batchNum + ': Memproses ' + Math.min(50, remaining) + ' tagihan...';
            
            const batchData = new FormData();
            batchData.append('action', 'fix_all_cat2');
            batchData.append('offset', '0');
            batchData.append('limit', '50');
            
            const batchRes = await fetch('audit_vps.php', { method: 'POST', body: batchData });
            const batchJson = await batchRes.json();
            
            if (!batchJson.success) {
                progressBar.classList.remove('bg-emerald-500');
                progressBar.classList.add('bg-red-500');
                progressLabel.textContent = 'Error pada Batch ' + batchNum + ': ' + batchJson.message;
                progressDetail.innerHTML = '<button onclick="startMassiveFixCat2()" class="mt-2 bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-lg">Retry dari Batch ' + batchNum + '</button>';
                return;
            }
            
            totalProcessed += batchJson.processed;
            remaining = batchJson.remaining;
            
            const pct = Math.round(((total - remaining) / total) * 100);
            progressBar.style.width = pct + '%';
            progressPct.textContent = pct + '%';
            progressDetail.textContent = totalProcessed + ' / ' + total + ' tagihan selesai. Sisa: ' + remaining;
            
            if (batchJson.processed === 0) break;
        }
        
        // Step 5: Complete
        progressBar.style.width = '100%';
        progressBar.classList.remove('bg-emerald-500');
        progressBar.classList.add('bg-emerald-400');
        progressPct.textContent = '100%';
        progressLabel.textContent = 'Selesai! ' + totalProcessed + ' tagihan berhasil diperbaiki.';
        progressDetail.textContent = 'Halaman akan dimuat ulang dalam 3 detik...';
        
        setTimeout(() => { location.reload(); }, 3000);
        
    } catch (err) {
        alert('Kesalahan jaringan: ' + err.message);
        btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Perbaiki Semua Cat 2';
        btn.disabled = false;
    }
}

function filterStudents(tab) {
    let input = document.getElementById('search-' + tab).value.toLowerCase();
    sessionStorage.setItem('search_' + tab, input);
    
    let wrappers = document.querySelectorAll('.student-wrapper-' + tab);
    wrappers.forEach(w => {
        let text = w.getAttribute('data-search');
        if (text.includes(input)) {
            w.style.display = '';
        } else {
            w.style.display = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    let savedTab = sessionStorage.getItem('active_tab');
    if(savedTab) {
        switchTab(savedTab);
    }
    
    let searchAktif = sessionStorage.getItem('search_aktif');
    if(searchAktif) {
        let el = document.getElementById('search-aktif');
        if(el) { el.value = searchAktif; filterStudents('aktif'); }
    }
    
    let searchRiwayat = sessionStorage.getItem('search_riwayat');
    if(searchRiwayat) {
        let el = document.getElementById('search-riwayat');
        if(el) { el.value = searchRiwayat; filterStudents('riwayat'); }
    }
});

function switchTab(tab) {
    sessionStorage.setItem('active_tab', tab);
    if(tab === 'aktif') {
        document.getElementById('content-aktif').classList.remove('hidden');
        document.getElementById('content-riwayat').classList.add('hidden');
        document.getElementById('tab-aktif').className = 'px-6 py-3 text-sm font-bold text-blue-600 border-b-2 border-blue-600';
        document.getElementById('tab-riwayat').className = 'px-6 py-3 text-sm font-medium text-slate-500 hover:text-slate-700';
    } else {
        document.getElementById('content-aktif').classList.add('hidden');
        document.getElementById('content-riwayat').classList.remove('hidden');
        document.getElementById('tab-riwayat').className = 'px-6 py-3 text-sm font-bold text-blue-600 border-b-2 border-blue-600';
        document.getElementById('tab-aktif').className = 'px-6 py-3 text-sm font-medium text-slate-500 hover:text-slate-700';
    }
}

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
    if(!confirm('Anda yakin ingin membatalkan (rollback) perbaikan ini secara atomik?')) return;
    const oldText = btn.innerHTML;
    btn.innerHTML = 'ROLLBACK...';
    btn.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'revert_repair');
    formData.append('repair_id', repairId);
    
    fetch('audit_vps.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) { alert(data.message); location.reload(); }
        else { alert('Error: ' + data.message); btn.innerHTML = oldText; btn.disabled = false; }
    })
    .catch(err => { alert('Kesalahan jaringan'); btn.innerHTML = oldText; btn.disabled = false; });
}

function toggleSelectAll(tagHash, isChecked, stuHash) {
    const checkboxes = document.querySelectorAll('.chk-' + tagHash);
    checkboxes.forEach(cb => cb.checked = isChecked);
    updateBulkCount(stuHash);
}

function updateBulkCount(stuHash) {
    const checkboxes = document.querySelectorAll('.chk-group-' + stuHash + ':checked');
    const count = checkboxes.length;
    const btn = document.querySelector('.btn-bulk-' + stuHash);
    const countSpan = document.querySelector('.count-' + stuHash);
    
    countSpan.innerHTML = count;
    if (count > 0) {
        btn.classList.remove('hidden');
    } else {
        btn.classList.add('hidden');
    }
}

function fixMultiAnomaly(stuHash, btn) {
    const checkboxes = document.querySelectorAll('.chk-group-' + stuHash + ':checked');
    if (checkboxes.length === 0) return;
    
    if(!confirm('Anda yakin ingin mengupdate kolektif (' + checkboxes.length + ' kartu) yang dipilih?')) return;
    
    const items = [];
    checkboxes.forEach(cb => {
        items.push({ bill_id: cb.value, cat: cb.getAttribute('data-cat') });
    });
    
    const oldText = btn.innerHTML;
    btn.innerHTML = 'UPDATING...';
    btn.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'fix_repair_multi');
    formData.append('items', JSON.stringify(items));
    
    fetch('audit_vps.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            btn.innerHTML = 'BERHASIL ✓';
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            btn.classList.add('bg-emerald-600');
            setTimeout(() => { location.reload(); }, 1500);
        } else {
            btn.innerHTML = oldText;
            btn.disabled = false;
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        btn.innerHTML = oldText;
        btn.disabled = false;
        alert('Terjadi kesalahan jaringan');
    });
}

function fixAnomaly(billId, category, btn) {
    if(!confirm('Anda yakin ingin memperbaiki (fix validkan) anomali ini? Aksi ini akan mengupdate database VPS secara langsung dan atomik.')) return;
    const oldText = btn.innerHTML;
    btn.innerHTML = 'FIXING...';
    btn.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'fix_repair');
    formData.append('bill_id', billId);
    formData.append('category', category);
    
    fetch('audit_vps.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            btn.innerHTML = 'FIXED ✓';
            btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            btn.classList.add('bg-emerald-600');
            // Auto switch tab
            setTimeout(() => { location.reload(); }, 1000);
        } else {
            btn.innerHTML = oldText;
            btn.disabled = false;
            alert('Error: ' + data.message);
        }
    })
    .catch(err => {
        btn.innerHTML = oldText;
        btn.disabled = false;
        alert('Terjadi kesalahan jaringan');
    });
}
</script>
</body>
</html>

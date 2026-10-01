<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BillingConsistencyAuditService;
use Illuminate\Support\Facades\DB;

class AuditBillingConsistencyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:audit-consistency
                            {--fix : Jalankan perbaikan otomatis (auto-heal) terhadap anomali yang aman}
                            {--dry-run : Simulasikan perbaikan tanpa mengubah database}
                            {--module= : Jalankan audit pada modul tertentu (1-6)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit menyeluruh integritas 6 item tagihan, tarif, mass billing, entri pembayaran, tagihan hantu, dan status lunas.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("================================================================");
        $this->info("   MEKANISME CEK AUDIT KONSISTENSI TAGIHAN & KEUANGAN (6 ITEM)  ");
        $this->info("================================================================");

        $isFix = $this->option('fix');
        $isDryRun = $this->option('dry-run');
        $selectedModule = $this->option('module');

        if ($isDryRun) {
            $this->warn("--> MODE: DRY-RUN (Simulasi saja, tidak ada data yang diubah di database)");
        } elseif ($isFix) {
            $this->warn("--> MODE: FIX / AUTO-HEAL (Perbaikan data otomatis akan dieksekusi)");
        } else {
            $this->line("--> MODE: AUDIT ONLY (Pengecekan dan pelaporan diagnostik)");
        }

        $this->newLine();
        $this->info("Sedang memindai integritas seluruh database...");

        $auditResults = BillingConsistencyAuditService::runFullAudit();

        // 1. TAMPILKAN TABEL RINGKASAN
        $summaryTable = [];
        $totalIssues = 0;

        foreach ($auditResults as $key => $item) {
            $statusBadge = $item['status'] === 'HEALTHY' ? '<info>HEALTHY (0)</info>' : "<fg=red>WARNING ({$item['issues_count']} Issue)</>";
            $summaryTable[] = [
                'Modul' => $item['name'],
                'Status' => $statusBadge,
                'Keterangan' => $this->formatSummaryNotes($key, $item['details']),
            ];
            $totalIssues += $item['issues_count'];
        }

        $this->table(['Item Area', 'Status Integritas', 'Ringkasan Diagnostik'], $summaryTable);

        // 2. TAMPILKAN DETAIL MASALAH JIKA ADA
        $this->newLine();
        if ($totalIssues === 0) {
            $this->info("🎉 LUAR BIASA! Seluruh data tagihan dan keuangan berada dalam status 100% KONSISTEN.");
            return Command::SUCCESS;
        }

        $this->warn("Ditemukan total {$totalIssues} anomali/potensi inkonsistensi yang perlu diperhatikan:");
        $this->printDetailIssues($auditResults, $selectedModule);

        // 3. JIKA OPSI --fix ATAU --dry-run DIAKTIFKAN, JALANKAN PERBAIKAN
        if ($isFix || $isDryRun) {
            $this->newLine();
            $this->info("Mengeksekusi perbaikan transaksional (" . ($isDryRun ? "DRY-RUN" : "LIVE FIX") . ")...");

            $repairOptions = [
                'fix_overpaid' => true,
                'fix_ghost_inactive' => true,
                'fix_ghost_deleted' => true,
                'relink_rate_items' => true,
                'backfill_details' => true,
            ];

            DB::beginTransaction();
            try {
                $repairResults = BillingConsistencyAuditService::repair($repairOptions, $isDryRun);

                $repairTable = [];
                foreach ($repairResults as $k => $res) {
                    $repairTable[] = [
                        'Tindakan Perbaikan' => $res['action'],
                        'Jumlah Data Diperbaiki' => $res['count'],
                        'Mode' => $res['dry_run'] ? 'Simulasi (Dry-Run)' : 'Tersimpan (Live)',
                    ];
                }

                $this->table(['Tindakan Perbaikan', 'Record Terdampak', 'Mode'], $repairTable);

                if ($isDryRun) {
                    DB::rollBack();
                    $this->info("✅ Simulasi Dry-Run selesai dengan aman (DB::rollBack). Tidak ada data yang diubah.");
                } else {
                    DB::commit();
                    $this->info("✅ Seluruh tindakan perbaikan konsistensi berhasil disimpan ke database!");
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error("❌ Terjadi kegagalan saat perbaikan: " . $e->getMessage());
                return Command::FAILURE;
            }
        } else {
            $this->newLine();
            $this->line("<comment>Tips:</comment> Untuk mensimulasikan perbaikan aman terhadap tagihan overpaid, tagihan hantu siswa non-aktif, dan backfill detail transaksi:");
            $this->line("  Jalankan: <info>php artisan bills:audit-consistency --dry-run</info>");
            $this->line("Untuk mengeksekusi perbaikan live:");
            $this->line("  Jalankan: <info>php artisan bills:audit-consistency --fix</info>");
        }

        return Command::SUCCESS;
    }

    private function formatSummaryNotes(string $key, array $details): string
    {
        switch ($key) {
            case 'item_1_bill_types':
                $dupCount = count($details['duplicate_groups']);
                return "Duplikat Nama: {$dupCount} grup | Null POS: {$details['null_pos_bayar']} | Null AY: {$details['null_academic_year']}";
            case 'item_2_payment_rates':
                $emptyItems = count($details['rates_without_items']);
                $noTarget = count($details['rates_without_target']);
                return "Tarif Tanpa Item Bulan: {$emptyItems} | Tarif Tanpa Target: {$noTarget}";
            case 'item_3_mass_billing':
                $zero = count($details['students_zero_bills']);
                return "Siswa Tanpa Tagihan: {$zero} | Belum Dapat Tagihan Bulanan TA Aktif: {$details['students_missing_active_ay_monthly']}";
            case 'item_4_transaction_details':
                return "Detail Nominal Kosong: {$details['null_or_zero_amounts']} | Virtual ID: {$details['virtual_bill_ids']} | Referensi Yatim: {$details['orphan_bill_references']}";
            case 'item_5_ghost_bills':
                return "Tagihan Siswa Terhapus: {$details['bills_on_deleted_students']} | UNPAID Siswa Non-Aktif: {$details['unpaid_bills_on_inactive_students']} | Null RateItem: {$details['null_payment_rate_item_id']}";
            case 'item_6_payment_consistency':
                $overpaid = count($details['overpaid_bills']);
                return "Overpaid (paid > amount): {$overpaid} | False Paid: {$details['false_paid_status']} | False Unpaid: {$details['false_unpaid_status']}";
            default:
                return "-";
        }
    }

    private function printDetailIssues(array $auditResults, ?string $selectedModule): void
    {
        // Item 1
        if ((!$selectedModule || $selectedModule == '1') && !empty($auditResults['item_1_bill_types']['details']['duplicate_groups'])) {
            $this->line("<fg=yellow>[1. Jenis Tagihan Duplikat pada Tahun Ajaran Sama]</>");
            foreach ($auditResults['item_1_bill_types']['details']['duplicate_groups'] as $dup) {
                $this->line("  - {$dup->name} ({$dup->academic_year_name}) : {$dup->count} duplicate records");
            }
        }

        // Item 2
        if ((!$selectedModule || $selectedModule == '2') && !empty($auditResults['item_2_payment_rates']['details']['rates_without_items'])) {
            $this->line("<fg=yellow>[2. Tarif Tanpa Item Bulan (Kosong)]</>");
            foreach (array_slice($auditResults['item_2_payment_rates']['details']['rates_without_items'], 0, 5) as $r) {
                $this->line("  - Rate #{$r->id} ({$r->bill_type_name})");
            }
            if (count($auditResults['item_2_payment_rates']['details']['rates_without_items']) > 5) {
                $this->line("    ... dan " . (count($auditResults['item_2_payment_rates']['details']['rates_without_items']) - 5) . " tarif lainnya.");
            }
        }

        // Item 3
        if ((!$selectedModule || $selectedModule == '3') && !empty($auditResults['item_3_mass_billing']['details']['students_zero_bills'])) {
            $this->line("<fg=yellow>[3. Siswa Aktif Tanpa Tagihan Sama Sekali]</>");
            foreach ($auditResults['item_3_mass_billing']['details']['students_zero_bills'] as $s) {
                $this->line("  - {$s->name} (NIS: {$s->nis}, Kelas: {$s->classroom_name})");
            }
        }

        // Item 6
        if ((!$selectedModule || $selectedModule == '6') && !empty($auditResults['item_6_payment_consistency']['details']['overpaid_bills'])) {
            $this->line("<fg=yellow>[6. Tagihan Overpaid (paid_amount > amount)]</>");
            foreach ($auditResults['item_6_payment_consistency']['details']['overpaid_bills'] as $o) {
                $this->line("  - {$o->student_name} | {$o->bill_name} | Tagihan: Rp " . number_format($o->amount, 0, ',', '.') . " | Terbayar: Rp " . number_format($o->paid_amount, 0, ',', '.') . " | Surplus: Rp " . number_format($o->over_amount, 0, ',', '.'));
            }
        }
    }
}

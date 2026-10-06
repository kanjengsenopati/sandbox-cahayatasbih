<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\BillType;
use App\Models\PaymentRate;
use App\Models\PaymentRateItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InjectZarkasiMonthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:inject-zarkasi-month
                            {--name=ZARKASI : Filter nama jenis bayar (contoh: ZARKASI, ZARKASI SMP)}
                            {--month=7 : Bulan yang di-inject (1-12, default 7 untuk Juli)}
                            {--year= : Tahun target (opsional, default start_year tahun ajaran)}
                            {--academic-year-id= : ID Tahun Ajaran spesifik (opsional)}
                            {--dry-run : Simulasi tanpa menyimpan perubahan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inject atau sesuaikan Bulan (default: Juli / 7) pada tarif & tagihan Zarkasi tanpa merusak pembayaran yang sudah masuk.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $nameFilter = $this->option('name') ?? 'ZARKASI';
        $targetMonth = (int) ($this->option('month') ?? 7);
        $customYear = $this->option('year');
        $academicYearId = $this->option('academic-year-id');
        $isDryRun = $this->option('dry-run');

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $targetMonthName = $monthNames[$targetMonth] ?? "Bulan $targetMonth";

        $this->info("=== INJECT BULAN UNTUK TARIF & TAGIHAN: {$nameFilter} ===");
        $this->line("Target Bulan : {$targetMonth} ({$targetMonthName})");
        if ($isDryRun) {
            $this->warn("MODE: DRY-RUN (Tidak ada perubahan yang disimpan ke database)");
        }

        $billTypes = BillType::with('academicYear')
            ->where('name', 'LIKE', "%{$nameFilter}%")
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->get();

        if ($billTypes->isEmpty()) {
            $this->error("Tidak ditemukan Jenis Bayar dengan nama mengandung '{$nameFilter}'.");
            return Command::FAILURE;
        }

        $totalRateItemsUpdated = 0;
        $totalBillsUpdated = 0;

        foreach ($billTypes as $bt) {
            $this->line("");
            $this->info("-> Memproses BillType: {$bt->name} (ID: {$bt->id})");
            $startYear = (int) ($customYear ?? ($bt->academicYear?->start_year ?? date('Y')));

            $rates = PaymentRate::with(['paymentRateItems', 'paymentRateClassrooms', 'paymentRateStudents'])
                ->where('bill_type_id', $bt->id)
                ->get();

            if ($rates->isEmpty()) {
                $this->warn("   Tidak ada tarif (PaymentRate) yang terdaftar untuk {$bt->name}.");
                continue;
            }

            foreach ($rates as $rate) {
                $rateItems = $rate->paymentRateItems;
                $matchingItem = $rateItems->firstWhere('month', $targetMonth);

                if (!$matchingItem) {
                    // Cek apakah ada item lama dengan bulan 0 atau bulan lain
                    $existingItemWithoutTarget = $rateItems->where('month', '!=', $targetMonth)->first();

                    if ($existingItemWithoutTarget) {
                        $this->line("   [RATE ITEM] Mengubah bulan item lama (ID: {$existingItemWithoutTarget->id}) dari {$existingItemWithoutTarget->month} -> {$targetMonth}");
                        if (!$isDryRun) {
                            $existingItemWithoutTarget->update([
                                'month' => $targetMonth,
                                'year' => $startYear,
                            ]);
                        }
                        $matchingItem = $existingItemWithoutTarget;
                    } else {
                        $this->line("   [RATE ITEM] Membuat item tarif baru untuk bulan {$targetMonth}/{$startYear} dengan nominal Rp " . number_format($rate->amount));
                        if (!$isDryRun) {
                            $matchingItem = $rate->paymentRateItems()->create([
                                'month' => $targetMonth,
                                'year' => $startYear,
                                'amount' => $rate->amount,
                            ]);
                        }
                    }
                    $totalRateItemsUpdated++;
                } else {
                    $this->line("   [RATE ITEM] Item tarif bulan {$targetMonth} sudah ada (ID: {$matchingItem->id}).");
                }

                // Perbarui semua tagihan siswa untuk bill_type ini yang bulannya belum sesuai
                $billsToUpdate = Bill::where('bill_type_id', $bt->id)
                    ->where(function($q) use ($targetMonth) {
                        $q->whereNull('month')
                          ->orWhere('month', 0)
                          ->orWhere('month', '!=', $targetMonth);
                    })
                    ->whereNull('deleted_at')
                    ->get();

                if ($billsToUpdate->isNotEmpty()) {
                    $this->line("   [BILLS] Menemukan {$billsToUpdate->count()} tagihan dengan bulan belum sesuai. Mengupdate ke bulan {$targetMonth}...");
                    if (!$isDryRun) {
                        foreach ($billsToUpdate as $b) {
                            $updateData = [
                                'month' => $targetMonth,
                                'year' => $startYear,
                            ];
                            if ($matchingItem && (!$b->payment_rate_item_id || $b->payment_rate_item_id !== $matchingItem->id)) {
                                $updateData['payment_rate_item_id'] = $matchingItem->id;
                            }
                            $b->update($updateData);
                            $totalBillsUpdated++;
                        }
                    } else {
                        $totalBillsUpdated += $billsToUpdate->count();
                    }
                }
            }

            // Sync student bills secara otomatis
            if (!$isDryRun) {
                $this->line("   [SYNC] Menjalankan sinkronisasi tagihan siswa (bills:sync-rate)...");
                try {
                    Artisan::call('bills:sync-rate', [
                        '--bill-type' => $bt->id,
                        '--force' => true,
                    ]);
                    $this->info("   [SYNC OK] Sinkronisasi tagihan siswa untuk {$bt->name} selesai.");
                } catch (\Throwable $e) {
                    $this->warn("   [SYNC WARNING] " . $e->getMessage());
                }
            }
        }

        if (!$isDryRun) {
            Cache::flush();
        }

        $this->line("");
        $this->info("=== SELESAI ===");
        $this->line("Total PaymentRateItems disesuaikan: {$totalRateItemsUpdated}");
        $this->line("Total Bills disesuaikan           : {$totalBillsUpdated}");
        $this->line("Cache aplikasi berhasil dibersihkan (Cache::flush).");

        return Command::SUCCESS;
    }
}

<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\BillType;
use App\Models\PaymentRate;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillingConsistencyAuditService
{
    /**
     * Jalankan audit menyeluruh terhadap 6 area tagihan dan keuangan.
     *
     * @return array
     */
    public static function runFullAudit(): array
    {
        return [
            'item_1_bill_types' => self::auditBillTypes(),
            'item_2_payment_rates' => self::auditPaymentRates(),
            'item_3_mass_billing' => self::auditMassBillingCoverage(),
            'item_4_transaction_details' => self::auditTransactionDetails(),
            'item_5_ghost_bills' => self::auditGhostBills(),
            'item_6_payment_consistency' => self::auditPaymentConsistency(),
        ];
    }

    /**
     * [ITEM 1] Audit Jenis Tagihan & POS Bayar
     */
    public static function auditBillTypes(): array
    {
        $totalActive = BillType::whereNull('deleted_at')->count();
        $nullItemCount = BillType::whereNull('deleted_at')->whereNull('bill_item_id')->count();
        $nullAyCount = BillType::whereNull('deleted_at')->whereNull('academic_year_id')->count();

        $duplicates = DB::select("
            SELECT bt.name, bt.academic_year_id, ay.name as academic_year_name, COUNT(*) as count, GROUP_CONCAT(bt.id) as ids
            FROM bill_types bt
            LEFT JOIN academic_years ay ON ay.id = bt.academic_year_id
            WHERE bt.deleted_at IS NULL
            GROUP BY bt.name, bt.academic_year_id, ay.name
            HAVING COUNT(*) > 1
        ");

        $issueCount = $nullItemCount + $nullAyCount + count($duplicates);

        return [
            'name' => 'Jenis Tagihan (Bill Types)',
            'status' => $issueCount === 0 ? 'HEALTHY' : 'WARNING',
            'total_records' => $totalActive,
            'issues_count' => $issueCount,
            'details' => [
                'null_pos_bayar' => $nullItemCount,
                'null_academic_year' => $nullAyCount,
                'duplicate_groups' => $duplicates,
            ]
        ];
    }

    /**
     * [ITEM 2] Audit Tarif Pembayaran (Payment Rates)
     */
    public static function auditPaymentRates(): array
    {
        $totalRates = PaymentRate::whereNull('deleted_at')->count();

        // Tarif tanpa PaymentRateItem sama sekali
        $ratesWithoutItems = DB::select("
            SELECT pr.id, pr.bill_type_id, bt.name as bill_type_name
            FROM payment_rates pr
            LEFT JOIN bill_types bt ON bt.id = pr.bill_type_id
            LEFT JOIN payment_rate_items pri ON pri.payment_rate_id = pr.id
            WHERE pr.deleted_at IS NULL
            GROUP BY pr.id, pr.bill_type_id, bt.name
            HAVING COUNT(pri.id) = 0
        ");

        // Tarif tanpa target kelas ataupun siswa
        $ratesWithoutTarget = DB::select("
            SELECT pr.id, pr.type, bt.name as bill_type_name
            FROM payment_rates pr
            LEFT JOIN bill_types bt ON bt.id = pr.bill_type_id
            LEFT JOIN payment_rate_classrooms prc ON prc.payment_rate_id = pr.id
            LEFT JOIN payment_rate_students prs ON prs.payment_rate_id = pr.id
            WHERE pr.deleted_at IS NULL
            GROUP BY pr.id, pr.type, bt.name
            HAVING COUNT(prc.id) = 0 AND COUNT(prs.id) = 0
        ");

        $issueCount = count($ratesWithoutItems) + count($ratesWithoutTarget);

        return [
            'name' => 'Tarif Pembayaran (Payment Rates)',
            'status' => $issueCount === 0 ? 'HEALTHY' : 'WARNING',
            'total_records' => $totalRates,
            'issues_count' => $issueCount,
            'details' => [
                'rates_without_items' => $ratesWithoutItems,
                'rates_without_target' => $ratesWithoutTarget,
            ]
        ];
    }

    /**
     * [ITEM 3] Audit Cakupan Mass Billing ke Seluruh Siswa
     */
    public static function auditMassBillingCoverage(): array
    {
        $activeStudentsCount = Student::where('status', 'ACTIVE')->whereNull('deleted_at')->count();

        // Siswa aktif tanpa tagihan sama sekali
        $studentsWithoutBills = DB::select("
            SELECT s.id, s.name, s.nis, c.name as classroom_name, sch.name as school_name
            FROM students s
            LEFT JOIN classrooms c ON c.id = s.classroom_id
            LEFT JOIN schools sch ON sch.id = s.school_id
            LEFT JOIN bills b ON b.student_id = s.id AND b.deleted_at IS NULL
            WHERE s.status = 'ACTIVE' AND s.deleted_at IS NULL
            GROUP BY s.id, s.name, s.nis, c.name, sch.name
            HAVING COUNT(b.id) = 0
        ");

        $activeAy = AcademicYear::where('is_active', true)->first();
        $missingActiveYearMonthly = 0;
        if ($activeAy) {
            $missingActiveYearMonthly = DB::select("
                SELECT COUNT(DISTINCT s.id) as count
                FROM students s
                WHERE s.status = 'ACTIVE' AND s.deleted_at IS NULL
                  AND NOT EXISTS (
                      SELECT 1 FROM bills b
                      JOIN bill_types bt ON bt.id = b.bill_type_id
                      WHERE b.student_id = s.id 
                        AND b.academic_year_id = ?
                        AND bt.type = 'MONTHLY'
                        AND b.deleted_at IS NULL
                  )
            ", [$activeAy->id])[0]->count ?? 0;
        }

        $issueCount = count($studentsWithoutBills) + $missingActiveYearMonthly;

        return [
            'name' => 'Cakupan Mass Billing (Coverage)',
            'status' => $issueCount === 0 ? 'HEALTHY' : 'WARNING',
            'total_active_students' => $activeStudentsCount,
            'issues_count' => $issueCount,
            'details' => [
                'students_zero_bills' => $studentsWithoutBills,
                'students_missing_active_ay_monthly' => $missingActiveYearMonthly,
                'active_academic_year' => $activeAy ? $activeAy->name : 'None',
            ]
        ];
    }

    /**
     * [ITEM 4] Audit Detail Entri Transaksi Pembayaran
     */
    public static function auditTransactionDetails(): array
    {
        $totalDetails = DB::table('transaction_details')->whereNull('deleted_at')->count();

        // Detail transaksi yang nominalnya NULL atau 0
        $nullAmountsCount = DB::table('transaction_details')
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('amount')->orWhere('amount', '<=', 0);
            })
            ->count();

        // Detail transaksi dengan ID virtual (generated_ atau auto_)
        $virtualDetailsCount = DB::table('transaction_details')
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('bill_id', 'like', 'generated_%')
                  ->orWhere('bill_id', 'like', 'auto_%');
            })
            ->count();

        // Detail transaksi yang mereferensikan tagihan yang tidak ada di tabel bills
        $orphanBillsCount = DB::select("
            SELECT COUNT(*) as count
            FROM transaction_details td
            LEFT JOIN bills b ON b.id = td.bill_id
            WHERE td.deleted_at IS NULL 
              AND td.bill_id IS NOT NULL 
              AND b.id IS NULL
        ")[0]->count ?? 0;

        $issueCount = $nullAmountsCount + $virtualDetailsCount + $orphanBillsCount;

        return [
            'name' => 'Entri Data Pembayaran (Transaction Details)',
            'status' => $issueCount === 0 ? 'HEALTHY' : 'WARNING',
            'total_records' => $totalDetails,
            'issues_count' => $issueCount,
            'details' => [
                'null_or_zero_amounts' => $nullAmountsCount,
                'virtual_bill_ids' => $virtualDetailsCount,
                'orphan_bill_references' => $orphanBillsCount,
            ]
        ];
    }

    /**
     * [ITEM 5] Audit Tagihan Hantu (Ghost Billing)
     */
    public static function auditGhostBills(): array
    {
        $totalBills = Bill::whereNull('deleted_at')->count();

        // 1. Tagihan pada siswa yang sudah terhapus permanen atau soft-deleted
        $deletedStudentBills = DB::select("
            SELECT COUNT(*) as count
            FROM bills b
            LEFT JOIN students s ON s.id = b.student_id
            WHERE b.deleted_at IS NULL AND (s.id IS NULL OR s.deleted_at IS NOT NULL)
        ")[0]->count ?? 0;

        // 2. Tagihan UNPAID pada siswa yang statusnya bukan aktif (Lulus, Keluar, Mutasi)
        $inactiveStudentUnpaidBills = DB::select("
            SELECT COUNT(*) as count
            FROM bills b
            JOIN students s ON s.id = b.student_id
            WHERE b.deleted_at IS NULL 
              AND b.status = 'UNPAID' 
              AND (b.paid_amount = 0 OR b.paid_amount IS NULL)
              AND s.status NOT IN ('ACTIVE', 1)
              AND s.deleted_at IS NULL
        ")[0]->count ?? 0;

        // 3. Tagihan dengan payment_rate_item_id NULL
        $nullRateItemBills = DB::table('bills')
            ->whereNull('deleted_at')
            ->whereNull('payment_rate_item_id')
            ->count();

        // 4. Tagihan duplikat (siswa sama, tipe sama, bulan sama, tahun sama di AY sama)
        $duplicateBillGroups = DB::select("
            SELECT student_id, bill_type_id, academic_year_id, month, year, COUNT(*) as count
            FROM bills
            WHERE deleted_at IS NULL
            GROUP BY student_id, bill_type_id, academic_year_id, month, year
            HAVING COUNT(*) > 1
        ");

        $issueCount = $deletedStudentBills + $inactiveStudentUnpaidBills + $nullRateItemBills + count($duplicateBillGroups);

        return [
            'name' => 'Tagihan Hantu (Ghost Billing)',
            'status' => $issueCount === 0 ? 'HEALTHY' : 'WARNING',
            'total_records' => $totalBills,
            'issues_count' => $issueCount,
            'details' => [
                'bills_on_deleted_students' => $deletedStudentBills,
                'unpaid_bills_on_inactive_students' => $inactiveStudentUnpaidBills,
                'null_payment_rate_item_id' => $nullRateItemBills,
                'duplicate_bill_groups' => count($duplicateBillGroups),
            ]
        ];
    }

    /**
     * [ITEM 6] Audit Konsistensi dan Persistensi Status Tagihan
     */
    public static function auditPaymentConsistency(): array
    {
        $totalBills = Bill::whereNull('deleted_at')->count();

        // 1. Tagihan Overpaid (paid_amount > amount)
        $overpaidBills = DB::select("
            SELECT b.id, s.name as student_name, bt.name as bill_name, b.amount, b.paid_amount, (b.paid_amount - b.amount) as over_amount
            FROM bills b
            JOIN students s ON s.id = b.student_id
            JOIN bill_types bt ON bt.id = b.bill_type_id
            WHERE b.deleted_at IS NULL AND b.paid_amount > b.amount
        ");

        // 2. Status 'PAID' tapi paid_amount < amount
        $falsePaidStatus = DB::select("
            SELECT id, student_id, amount, paid_amount, status
            FROM bills
            WHERE deleted_at IS NULL 
              AND status = 'PAID' 
              AND paid_amount < amount
        ");

        // 3. Status 'UNPAID' tapi paid_amount >= amount
        $falseUnpaidStatus = DB::select("
            SELECT id, student_id, amount, paid_amount, status
            FROM bills
            WHERE deleted_at IS NULL 
              AND status = 'UNPAID' 
              AND paid_amount >= amount AND amount > 0
        ");

        $issueCount = count($overpaidBills) + count($falsePaidStatus) + count($falseUnpaidStatus);

        return [
            'name' => 'Konsistensi Status & Nominal Tagihan',
            'status' => $issueCount === 0 ? 'HEALTHY' : 'WARNING',
            'total_records' => $totalBills,
            'issues_count' => $issueCount,
            'details' => [
                'overpaid_bills' => $overpaidBills,
                'false_paid_status' => count($falsePaidStatus),
                'false_unpaid_status' => count($falseUnpaidStatus),
            ]
        ];
    }

    /**
     * Eksekusi Perbaikan Otomatis (Auto-Heal / Repair) secara aman.
     *
     * @param array $options ['fix_overpaid', 'fix_ghost_inactive', 'fix_ghost_deleted', 'fix_virtual_details', 'relink_rate_items', 'backfill_details']
     * @param bool $dryRun
     * @return array
     */
    public static function repair(array $options = [], bool $dryRun = false): array
    {
        $results = [];

        // 1. FIX OVERPAID BILLS: Selaraskan amount dengan paid_amount agar status PAID konsisten
        if (!empty($options['fix_overpaid'])) {
            $overpaid = DB::select("
                SELECT id, amount, paid_amount 
                FROM bills 
                WHERE deleted_at IS NULL AND paid_amount > amount
            ");

            $fixedCount = 0;
            if (!$dryRun) {
                foreach ($overpaid as $row) {
                    DB::table('bills')->where('id', $row->id)->update([
                        'amount' => $row->paid_amount,
                        'status' => Bill::STATUS_PAID,
                        'updated_at' => now(),
                    ]);
                    $fixedCount++;
                }
            } else {
                $fixedCount = count($overpaid);
            }
            $results['fix_overpaid'] = [
                'action' => 'Menyelaraskan nilai amount tagihan overpaid agar setara paid_amount (Lunas)',
                'count' => $fixedCount,
                'dry_run' => $dryRun,
            ];
        }

        // 2. FIX GHOST BILLS ON INACTIVE STUDENTS: Soft-delete UNPAID bills untuk siswa non-aktif/lulus/keluar
        if (!empty($options['fix_ghost_inactive'])) {
            $inactiveStudentBills = DB::select("
                SELECT b.id
                FROM bills b
                JOIN students s ON s.id = b.student_id
                WHERE b.deleted_at IS NULL 
                  AND b.status = 'UNPAID' 
                  AND (b.paid_amount = 0 OR b.paid_amount IS NULL)
                  AND s.status NOT IN ('ACTIVE', 1)
                  AND s.deleted_at IS NULL
            ");

            $ids = array_column($inactiveStudentBills, 'id');
            $fixedCount = 0;

            if (!$dryRun && !empty($ids)) {
                $chunks = array_chunk($ids, 500);
                foreach ($chunks as $chunk) {
                    DB::table('bills')->whereIn('id', $chunk)->update(['deleted_at' => now()]);
                    $fixedCount += count($chunk);
                }
            } else {
                $fixedCount = count($ids);
            }

            $results['fix_ghost_inactive'] = [
                'action' => 'Soft-delete tagihan UNPAID fiktif pada santri yang sudah keluar/lulus/non-aktif',
                'count' => $fixedCount,
                'dry_run' => $dryRun,
            ];
        }

        // 3. FIX GHOST BILLS ON DELETED STUDENTS: Soft-delete tagihan yang student_id-nya sudah terhapus
        if (!empty($options['fix_ghost_deleted'])) {
            $deletedStudentBills = DB::select("
                SELECT b.id
                FROM bills b
                LEFT JOIN students s ON s.id = b.student_id
                WHERE b.deleted_at IS NULL AND (s.id IS NULL OR s.deleted_at IS NOT NULL)
            ");

            $ids = array_column($deletedStudentBills, 'id');
            $fixedCount = 0;

            if (!$dryRun && !empty($ids)) {
                $chunks = array_chunk($ids, 500);
                foreach ($chunks as $chunk) {
                    DB::table('bills')->whereIn('id', $chunk)->update(['deleted_at' => now()]);
                    $fixedCount += count($chunk);
                }
            } else {
                $fixedCount = count($ids);
            }

            $results['fix_ghost_deleted'] = [
                'action' => 'Soft-delete tagihan yatim yang siswa tertargetnya sudah terhapus dari database',
                'count' => $fixedCount,
                'dry_run' => $dryRun,
            ];
        }

        // 4. RELINK NULL PAYMENT RATE ITEMS: Hubungkan kembali tagihan dengan item tarif resminya
        if (!empty($options['relink_rate_items'])) {
            $unlinkedBills = DB::select("
                SELECT b.id, b.bill_type_id, b.month, b.year
                FROM bills b
                WHERE b.deleted_at IS NULL AND b.payment_rate_item_id IS NULL
            ");

            $fixedCount = 0;
            if (!empty($unlinkedBills)) {
                // Preload payment rate items
                $rateItems = DB::table('payment_rate_items as pri')
                    ->join('payment_rates as pr', 'pr.id', '=', 'pri.payment_rate_id')
                    ->whereNull('pr.deleted_at')
                    ->select('pri.id', 'pr.bill_type_id', 'pri.month', 'pri.year')
                    ->get();

                $rateItemLookup = [];
                foreach ($rateItems as $ri) {
                    $key = "{$ri->bill_type_id}_{$ri->month}_{$ri->year}";
                    if (!isset($rateItemLookup[$key])) {
                        $rateItemLookup[$key] = $ri->id;
                    }
                }

                foreach ($unlinkedBills as $b) {
                    $key = "{$b->bill_type_id}_{$b->month}_{$b->year}";
                    if (isset($rateItemLookup[$key])) {
                        if (!$dryRun) {
                            DB::table('bills')->where('id', $b->id)->update([
                                'payment_rate_item_id' => $rateItemLookup[$key]
                            ]);
                        }
                        $fixedCount++;
                    }
                }
            }

            $results['relink_rate_items'] = [
                'action' => 'Menautkan kembali tagihan ber-rate_item_id NULL ke PaymentRateItem yang cocok',
                'count' => $fixedCount,
                'dry_run' => $dryRun,
            ];
        }

        // 5. BACKFILL TRANSACTION DETAILS AMOUNT: Isi nilai detail transaksi dari paid_amount tagihan
        if (!empty($options['backfill_details'])) {
            $nullDetails = DB::select("
                SELECT td.id, b.amount, b.paid_amount
                FROM transaction_details td
                JOIN bills b ON b.id = td.bill_id
                WHERE td.deleted_at IS NULL 
                  AND (td.amount IS NULL OR td.amount <= 0)
            ");

            $fixedCount = 0;
            if (!$dryRun && !empty($nullDetails)) {
                foreach ($nullDetails as $d) {
                    $val = ($d->paid_amount > 0) ? $d->paid_amount : $d->amount;
                    if ($val > 0) {
                        DB::table('transaction_details')->where('id', $d->id)->update(['amount' => $val]);
                        $fixedCount++;
                    }
                }
            } else {
                $fixedCount = count($nullDetails);
            }

            $results['backfill_details'] = [
                'action' => 'Mengisi nominal transaction_details.amount yang kosong dari data tagihan',
                'count' => $fixedCount,
                'dry_run' => $dryRun,
            ];
        }

        return $results;
    }
}

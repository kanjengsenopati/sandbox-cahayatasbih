<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            // 1. Soft-delete pemetaan tarif-kelas yang merujuk ke kelas yang sudah di-soft-delete
            $deletedClassroomIds = DB::table('classrooms')->whereNotNull('deleted_at')->pluck('id');
            if ($deletedClassroomIds->isNotEmpty()) {
                $affectedClassrooms = DB::table('payment_rate_classrooms')
                    ->whereIn('classroom_id', $deletedClassroomIds)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => now()]);
                echo "Soft-deleted {$affectedClassrooms} orphaned payment_rate_classrooms mappings.\n";
            }

            // 2. Soft-delete pemetaan tarif-siswa yang merujuk ke siswa yang sudah di-soft-delete
            $deletedStudentIds = DB::table('students')->whereNotNull('deleted_at')->pluck('id');
            if ($deletedStudentIds->isNotEmpty()) {
                $affectedStudents = DB::table('payment_rate_students')
                    ->whereIn('student_id', $deletedStudentIds)
                    ->whereNull('deleted_at')
                    ->update(['deleted_at' => now()]);
                echo "Soft-deleted {$affectedStudents} orphaned payment_rate_students mappings.\n";
            }

            // 3. Cari pemetaan kelas aktif ke beberapa tarif pembayaran untuk jenis bayar yang sama (Duplikasi Pemetaan)
            $duplicates = DB::table('payment_rate_classrooms as prc')
                ->join('payment_rates as pr', 'prc.payment_rate_id', '=', 'pr.id')
                ->whereNull('prc.deleted_at')
                ->whereNull('pr.deleted_at')
                ->select('pr.bill_type_id', 'prc.classroom_id', DB::raw('COUNT(*) as qty'))
                ->groupBy('pr.bill_type_id', 'prc.classroom_id')
                ->having('qty', '>', 1)
                ->get();

            echo "Found " . $duplicates->count() . " classrooms with duplicate payment rate mappings.\n";

            foreach ($duplicates as $dup) {
                // Ambil info nama kelas target
                $targetClassroom = DB::table('classrooms')->where('id', $dup->classroom_id)->first();
                if (!$targetClassroom) continue;

                $targetName = $targetClassroom->name;
                $targetGrade = $this->getGradePrefix($targetName);

                // Ambil semua pemetaan aktif untuk grup duplikat ini
                $mappings = DB::table('payment_rate_classrooms as prc')
                    ->join('payment_rates as pr', 'prc.payment_rate_id', '=', 'pr.id')
                    ->where('pr.bill_type_id', $dup->bill_type_id)
                    ->where('prc.classroom_id', $dup->classroom_id)
                    ->whereNull('prc.deleted_at')
                    ->whereNull('pr.deleted_at')
                    ->select('prc.id as mapping_id', 'pr.id as rate_id', 'pr.amount')
                    ->get();

                // Evaluasi kecocokan grade untuk menentukan pemetaan mana yang benar
                $bestMappingId = null;
                $highestScore = -1;
                $evaluatedMappings = [];

                foreach ($mappings as $m) {
                    // Cari kelas lain yang terhubung ke payment_rate_id yang sama
                    $otherClassroomNames = DB::table('payment_rate_classrooms as prc')
                        ->join('classrooms as c', 'prc.classroom_id', '=', 'c.id')
                        ->where('prc.payment_rate_id', $m->rate_id)
                        ->where('prc.classroom_id', '!=', $dup->classroom_id)
                        ->whereNull('prc.deleted_at')
                        ->whereNull('c.deleted_at')
                        ->pluck('c.name')
                        ->toArray();

                    // Hitung berapa banyak kelas lain yang memiliki tingkat kelas (grade) yang sama
                    $score = 0;
                    if ($targetGrade !== null) {
                        foreach ($otherClassroomNames as $otherName) {
                            if ($this->getGradePrefix($otherName) === $targetGrade) {
                                $score++;
                            }
                        }
                    }

                    $evaluatedMappings[] = [
                        'mapping' => $m,
                        'score' => $score,
                        'other_classes_count' => count($otherClassroomNames)
                    ];
                }

                // Urutkan berdasarkan score tertinggi, lalu jumlah kelas lainnya
                usort($evaluatedMappings, function ($a, $b) {
                    if ($a['score'] !== $b['score']) {
                        return $b['score'] <=> $a['score'];
                    }
                    return $b['other_classes_count'] <=> $a['other_classes_count'];
                });

                $correctMappingInfo = $evaluatedMappings[0];
                $correctMappingId = $correctMappingInfo['mapping']->mapping_id;
                $correctRateId = $correctMappingInfo['mapping']->rate_id;

                echo "Classroom '{$targetName}' (ID: {$dup->classroom_id}): Correct Rate ID is {$correctRateId} (Score: {$correctMappingInfo['score']}).\n";

                // Hapus pemetaan yang salah secara soft-delete
                foreach ($evaluatedMappings as $eval) {
                    $m = $eval['mapping'];
                    if ($m->mapping_id !== $correctMappingId) {
                        echo "  - Soft-deleting ghost mapping ID: {$m->mapping_id} (Rate ID: {$m->rate_id})\n";
                        DB::table('payment_rate_classrooms')
                            ->where('id', $m->mapping_id)
                            ->update(['deleted_at' => now()]);

                        // Soft-delete tagihan siswa (UNPAID) di kelas tersebut yang merujuk ke payment_rate_id yang salah
                        // Cari payment_rate_item_id yang terhubung dengan rate yang salah
                        $wrongRateItemIds = DB::table('payment_rate_items')
                            ->where('payment_rate_id', $m->rate_id)
                            ->pluck('id');

                        if ($wrongRateItemIds->isNotEmpty()) {
                            // Cari siswa aktif di kelas tersebut
                            $studentIdsInClass = DB::table('students')
                                ->where('classroom_id', $dup->classroom_id)
                                ->pluck('id');

                            if ($studentIdsInClass->isNotEmpty()) {
                                $deletedBills = DB::table('bills')
                                    ->whereIn('student_id', $studentIdsInClass)
                                    ->whereIn('payment_rate_item_id', $wrongRateItemIds)
                                    ->where('status', 'UNPAID')
                                    ->whereNull('deleted_at')
                                    ->update(['deleted_at' => now()]);

                                echo "    - Soft-deleted {$deletedBills} unpaid ghost bills belonging to students in classroom '{$targetName}'.\n";
                            }
                        }
                    }
                }
            }

            // 4. Bersihkan tagihan duplikat yang tersisa (jika ada siswa yang memiliki 2 tagihan aktif untuk periode/bulan yang sama)
            // (Untuk memastikan 100% steril dari duplikasi)
            $duplicateBills = DB::table('bills')
                ->whereNull('deleted_at')
                ->select('student_id', 'bill_type_id', 'month', 'year', DB::raw('COUNT(*) as qty'))
                ->groupBy('student_id', 'bill_type_id', 'month', 'year')
                ->having('qty', '>', 1)
                ->get();

            if ($duplicateBills->isNotEmpty()) {
                echo "Found {$duplicateBills->count()} duplicate bill groups. Pruning duplicate bills...\n";
                $prunedBillsCount = 0;

                foreach ($duplicateBills as $group) {
                    // Ambil semua tagihan aktif dalam kelompok duplikat ini
                    $bills = DB::table('bills')
                        ->where('student_id', $group->student_id)
                        ->where('bill_type_id', $group->bill_type_id)
                        ->where('month', $group->month)
                        ->where('year', $group->year)
                        ->whereNull('deleted_at')
                        ->get();

                    // Strategi pemangkasan:
                    // 1. Jika ada tagihan yang PAID, pertahankan tagihan tersebut dan soft-delete sisanya.
                    // 2. Jika tidak ada yang PAID, pertahankan tagihan terbaru (berdasarkan created_at) dan hapus sisanya.
                    $keepBill = null;
                    $paidBill = $bills->where('status', 'PAID')->first();

                    if ($paidBill) {
                        $keepBill = $paidBill;
                    } else {
                        // Urutkan dari yang terbaru
                        $sortedBills = $bills->sortByDesc('created_at');
                        $keepBill = $sortedBills->first();
                    }

                    foreach ($bills as $b) {
                        if ($b->id !== $keepBill->id) {
                            DB::table('bills')
                                ->where('id', $b->id)
                                ->update(['deleted_at' => now()]);
                            $prunedBillsCount++;
                        }
                    }
                }
                echo "Successfully pruned {$prunedBillsCount} duplicate student bills.\n";
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversible migration is destructive, leave empty
    }

    /**
     * Ambil prefiks tingkat kelas (angka pertama) dari nama kelas.
     * Contoh: "8F" -> 8, "9D" -> 9, "Kelas Dihapus" -> null.
     */
    private function getGradePrefix(?string $name): ?int
    {
        if (!$name) return null;
        if (preg_match('/^([0-9]+)/', $name, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }
};

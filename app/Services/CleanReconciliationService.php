<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanReconciliationService
{
    /**
     * Perform clean reconciliation for students of older classes (8, 9, 10, 11, 12, PPTQ)
     * by mirroring authoritative transactions from master and purging local test artifacts.
     *
     * @param array $options Options like ['class_id' => ..., 'student_id' => ..., 'school_id' => ...]
     * @param callable|null $progressCallback Optional callback for progress logging
     * @return array
     */
    public function execute(array $options = [], ?callable $progressCallback = null): array
    {
        @set_time_limit(3600);
        @ini_set('memory_limit', '2048M');

        $masterConn = DB::connection('mysql_master');
        $localConn = DB::connection();

        if ($localConn->getDriverName() === 'sqlite') {
            try {
                $localConn->statement("PRAGMA journal_mode = WAL;");
                $localConn->statement("PRAGMA busy_timeout = 60000;");
                $localConn->statement("PRAGMA synchronous = NORMAL;");
            } catch (\Throwable $e) {}
        }

        $log = function(string $msg) use ($progressCallback) {
            if ($progressCallback) {
                $progressCallback($msg);
            }
            Log::info("[CleanReconciliation] " . $msg);
        };

        $log("Memulai proses Rekonsiliasi Bersih (Clean Reconciliation)...");

        // 1. Identify Target Classrooms (Exclude Kelas 7 / New Students)
        $classQuery = $masterConn->table('classrooms')
            ->leftJoin('schools', 'classrooms.school_id', '=', 'schools.id')
            ->select('classrooms.id', 'classrooms.name', 'classrooms.school_id', 'schools.name as school_name');

        if (!empty($options['class_id'])) {
            $classQuery->where('classrooms.id', $options['class_id']);
        } elseif (!empty($options['school_id'])) {
            $classQuery->where('classrooms.school_id', $options['school_id']);
        } else {
            // Default target: All classes EXCEPT Kelas 7
            $classQuery->where(function($q) {
                $q->where('classrooms.name', 'regexp', '^(8|VIII|9|IX|10|X|11|XI|12|XII)')
                  ->orWhere('classrooms.name', 'like', '%PONDOK%')
                  ->orWhere('classrooms.name', 'like', '%PPTQ%')
                  ->orWhere('schools.name', 'like', '%PPTQ%')
                  ->orWhere('schools.name', 'like', '%MADRASAH ALIYAH%');
            });
        }

        $targetClasses = $classQuery->get();
        $targetClassIds = $targetClasses->pluck('id')->toArray();

        $log("Ditemukan " . count($targetClassIds) . " rombel/kelas target untuk rekonsiliasi bersih.");

        // 2. Identify Target Students
        $studentQuery = $masterConn->table('students')
            ->whereIn('classroom_id', $targetClassIds)
            ->whereNull('deleted_at');

        if (!empty($options['student_id'])) {
            $studentQuery->where('id', $options['student_id']);
        }

        $masterStudents = $studentQuery->get();
        $targetStudentIds = $masterStudents->pluck('id')->toArray();
        $totalStudents = count($targetStudentIds);

        $log("Total santri target dalam scope: {$totalStudents} santri.");

        if ($totalStudents === 0) {
            return [
                'status' => 'success',
                'message' => 'Tidak ada santri yang perlu direkonsiliasi.',
                'students_processed' => 0,
                'purged_test_histories' => 0,
                'synced_master_histories' => 0,
            ];
        }

        // 3. Process in Chunks of 50 students
        $chunks = array_chunk($targetStudentIds, 50);
        $totalPurged = 0;
        $totalSynced = 0;
        $totalStudentsUpdated = 0;

        foreach ($chunks as $chunkIndex => $studentChunk) {
            $log("Memproses Batch " . ($chunkIndex + 1) . "/" . count($chunks) . " (" . count($studentChunk) . " santri)...");

            // A. Fetch all master histories for this chunk using indexed query (no remote filesort)
            $masterHistories = $masterConn->table('saldo_histories')
                ->whereIn('student_id', $studentChunk)
                ->whereNull('deleted_at')
                ->get();

            $masterHistoryIds = $masterHistories->pluck('id')->toArray();
            $masterHistoryIdMap = array_flip($masterHistoryIds);

            // B. Find rogue/dummy local test records using PHP set difference and delete them in chunks of 500
            $localHistoryIds = $localConn->table('saldo_histories')
                ->whereIn('student_id', $studentChunk)
                ->pluck('id')
                ->toArray();

            $rogueIds = [];
            foreach ($localHistoryIds as $lid) {
                if (!isset($masterHistoryIdMap[$lid])) {
                    $rogueIds[] = $lid;
                }
            }

            $purgedDummy = count($rogueIds);
            if ($purgedDummy > 0) {
                foreach (array_chunk($rogueIds, 500) as $rogueChunk) {
                    $localConn->table('saldo_histories')->whereIn('id', $rogueChunk)->delete();
                }
                $log("   - Membersihkan {$purgedDummy} riwayat dummy test lokal.");
            }
            $totalPurged += $purgedDummy;

            // C. Bulk upsert all master histories for this chunk inside a transaction for instant execution
            $toInsert = [];
            foreach ($masterHistories as $mh) {
                $toInsert[] = (array) $mh;
            }

            if (!empty($toInsert)) {
                $localConn->transaction(function() use ($localConn, $toInsert) {
                    foreach (array_chunk($toInsert, 100) as $batch) {
                        $localConn->table('saldo_histories')->upsert(
                            $batch,
                            ['id'],
                            ['student_id', 'type', 'amount', 'usage', 'description', 'status', 'balance_before', 'balance_after', 'created_at', 'updated_at', 'deleted_at']
                        );
                    }
                });
            }
            $totalSynced += count($masterHistories);

            // C. Sync POS Transactions only if requested in options
            if (!empty($options['sync_pos'])) {
                $posMasterList = $masterConn->table('point_of_sale_transactions')
                    ->whereIn('student_id', $studentChunk)
                    ->whereNull('deleted_at')
                    ->get();

                if ($posMasterList->isNotEmpty()) {
                    $localExistingPosIds = $localConn->table('point_of_sale_transactions')
                        ->whereIn('student_id', $studentChunk)
                        ->pluck('id')
                        ->toArray();

                    if (!empty($localExistingPosIds)) {
                        $localConn->table('point_of_sale_transactions')->whereIn('id', $localExistingPosIds)->delete();
                    }

                    foreach (array_chunk($posMasterList->toArray(), 500) as $posBatch) {
                        $posInsertData = array_map(fn($item) => (array)$item, $posBatch);
                        $localConn->table('point_of_sale_transactions')->insert($posInsertData);
                    }
                }
            }

            // D. Update Student Saldo, Saving, and Timestamps in Local
            $chunkMasterStudents = $masterStudents->whereIn('id', $studentChunk);
            foreach ($chunkMasterStudents as $ms) {
                $localExists = $localConn->table('students')->where('id', $ms->id)->exists();
                if ($localExists) {
                    $localConn->table('students')->where('id', $ms->id)->update([
                        'saldo' => $ms->saldo,
                        'saving' => $ms->saving,
                        'updated_at' => $ms->updated_at,
                    ]);
                } else {
                    $localConn->table('students')->insert((array) $ms);
                }
                $totalStudentsUpdated++;
            }
        }

        $log("Rekonsiliasi Bersih Selesai!");
        $log("Hasil: {$totalStudentsUpdated} santri disinkronkan, {$totalPurged} riwayat dummy test dibersihkan, {$totalSynced} mutasi master asli disinkronkan.");

        return [
            'status' => 'success',
            'message' => 'Rekonsiliasi bersih berhasil diselesaikan.',
            'students_processed' => $totalStudentsUpdated,
            'purged_test_histories' => $totalPurged,
            'synced_master_histories' => $totalSynced,
        ];
    }
}

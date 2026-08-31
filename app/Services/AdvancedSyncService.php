<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdvancedSyncService
{
    /**
     * Generate preview of sync data without saving to local database.
     * 
     * @param array $filters (start_date, end_date, school_id, classroom_id, search)
     * @return array preview data and id
     */
    public function generatePreview(array $filters)
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '1024M');

        $previewId = 'sync_preview_' . (string) Str::uuid();
        $localConn = DB::connection();
        $masterConn = $this->getRemoteConnection();

        $masterQuery = $masterConn->table('students')
            ->select('students.id', 'students.name', 'students.nis', 'students.saldo', 'students.saving', 'students.created_at', 'students.updated_at', 'classrooms.name as classroom_name', 'schools.name as school_name')
            ->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
            ->leftJoin('schools', 'classrooms.school_id', '=', 'schools.id')
            ->whereNull('students.deleted_at');

        if (!empty($filters['school_id'])) {
            $masterQuery->where('classrooms.school_id', $filters['school_id']);
        }
        if (!empty($filters['classroom_id'])) {
            $masterQuery->where('students.classroom_id', $filters['classroom_id']);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $masterQuery->where(function($q) use ($search) {
                $q->where('students.name', 'like', '%' . $search . '%')
                  ->orWhere('students.nis', 'like', '%' . $search . '%');
            });
        }

        $studentsMaster = $masterQuery->get()->keyBy('id');
        $studentIds = $studentsMaster->keys()->toArray();

        if (empty($studentIds)) {
            return [
                'preview_id' => null,
                'data' => collect([])
            ];
        }

        // Fetch Local Students for comparison
        $studentsLocal = $localConn->table('students')
            ->select('id', 'saldo', 'created_at', 'updated_at')
            ->whereIn('id', $studentIds)
            ->get()->keyBy('id');


        // Fetch Master Saldo Histories in chunks of 200 students to prevent SQL binding limit / PDO slowdown
        $saldoHistoriesMasterList = collect();
        foreach (array_chunk($studentIds, 200) as $chunk) {
            $q = $masterConn->table('saldo_histories')
                ->select('id', 'student_id', 'type', 'amount', 'usage', 'description', 'status', 'balance_before', 'balance_after', 'created_at')
                ->whereIn('student_id', $chunk)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'asc');

            if (!empty($filters['start_date'])) {
                $q->where('created_at', '>=', $filters['start_date'] . ' 00:00:00');
            }
            if (!empty($filters['end_date'])) {
                $q->where('created_at', '<=', $filters['end_date'] . ' 23:59:59');
            }

            $saldoHistoriesMasterList = $saldoHistoriesMasterList->concat($q->get());
        }

        $saldoHistoriesMaster = $saldoHistoriesMasterList->groupBy('student_id');

        // Fetch Local Saldo Histories timestamps for display
        $localHistoriesByStudent = collect();
        foreach (array_chunk($studentIds, 200) as $chunk) {
            $lh = $localConn->table('saldo_histories')
                ->select('student_id', 'created_at')
                ->whereIn('student_id', $chunk)
                ->whereNull('deleted_at')
                ->get();
            $localHistoriesByStudent = $localHistoriesByStudent->concat($lh);
        }
        $localHistoriesByStudent = $localHistoriesByStudent->groupBy('student_id');

        $previewData = [];

        foreach ($studentsMaster as $studentId => $masterStudent) {
            $localStudent = $studentsLocal->get($studentId);
            $masterHistories = $saldoHistoriesMaster->get($studentId) ?? collect();
            $localHistories = $localHistoriesByStudent->get($studentId) ?? collect();

            // Show student if they have ANY master histories matching filter
            if ($masterHistories->isEmpty()) {
                continue;
            }

            $currentLocalSaldo = $localStudent ? $localStudent->saldo : 0;
            $masterSaldo = $masterStudent->saldo;
            
            // Single Source of Truth: Master saldo IS the final saldo, no merge calculation
            $saldoDifference = $masterSaldo - $currentLocalSaldo;

            // Determine sync status
            if (!$localStudent) {
                $conflictStatus = 'NEW';
            } elseif ($currentLocalSaldo == $masterSaldo) {
                $conflictStatus = 'OK';
            } else {
                $conflictStatus = 'NEEDS_SYNC';
            }

            // Determine timestamp for local saldo
            $localSaldoTimestamp = null;
            if ($localHistories->isNotEmpty()) {
                $localSaldoTimestamp = $localHistories->sortByDesc('created_at')->first()->created_at;
            } elseif ($localStudent) {
                $localSaldoTimestamp = $localStudent->updated_at ?? $localStudent->created_at;
            }

            // Determine timestamp for master saldo
            $masterSaldoTimestamp = null;
            if ($masterHistories->isNotEmpty()) {
                $masterSaldoTimestamp = $masterHistories->sortByDesc('created_at')->first()->created_at;
            } else {
                $masterSaldoTimestamp = $masterStudent->updated_at ?? $masterStudent->created_at;
            }

            $localSaldoDate = $localSaldoTimestamp ? \Carbon\Carbon::parse($localSaldoTimestamp)->format('d-M-Y') : null;
            $localSaldoTime = $localSaldoTimestamp ? \Carbon\Carbon::parse($localSaldoTimestamp)->format('H : i : s') : null;

            $masterSaldoDate = $masterSaldoTimestamp ? \Carbon\Carbon::parse($masterSaldoTimestamp)->format('d-M-Y') : null;
            $masterSaldoTime = $masterSaldoTimestamp ? \Carbon\Carbon::parse($masterSaldoTimestamp)->format('H : i : s') : null;

            $previewData[] = [
                'student_id' => $studentId,
                'nis' => $masterStudent->nis ?? '-',
                'name' => $masterStudent->name,
                'classroom' => $masterStudent->classroom_name ?? '-',
                'school' => $masterStudent->school_name ?? '-',
                'current_local_saldo' => $currentLocalSaldo,
                'local_saldo_date' => $localSaldoDate,
                'local_saldo_time' => $localSaldoTime,
                'master_saldo' => $masterSaldo,
                'master_saldo_date' => $masterSaldoDate,
                'master_saldo_time' => $masterSaldoTime,
                'saldo_difference' => $saldoDifference,
                'master_histories_count' => $masterHistories->count(),
                'conflict_status' => $conflictStatus,
                'histories_to_insert' => $masterHistories->values()->toArray()
            ];
        }

        if (!empty($previewData)) {
            // Cache for 30 minutes
            Cache::put($previewId, $previewData, now()->addMinutes(30));
        }

        return [
            'preview_id' => empty($previewData) ? null : $previewId,
            'data' => collect($previewData)
        ];
    }

    /**
     * Execute the sync by mirroring master data to local (Single Source of Truth).
     * This purges local-only histories and overwrites saldo from master.
     * 
     * @param string $previewId
     * @param string $adminId
     * @param array|null $selectedStudentIds
     * @return array result status
     */
    public function executeSync(string $previewId, string $adminId, ?array $selectedStudentIds = null)
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '512M');

        $previewData = Cache::get($previewId);

        if (!$previewData) {
            throw new \Exception("Data preview tidak ditemukan atau sudah kadaluarsa. Silakan ulangi filter preview.");
        }

        $localConn = DB::connection();
        $masterConn = $this->getRemoteConnection();

        $localConn->beginTransaction();

        try {
            $processedCount = 0;

            foreach ($previewData as $data) {
                $studentId = $data['student_id'];
                
                // If specific students are selected, skip those not in the list
                if ($selectedStudentIds !== null && !in_array((string)$studentId, $selectedStudentIds)) {
                    continue;
                }
                
                // Ensure student exists locally
                $studentExists = $localConn->table('students')->where('id', $studentId)->exists();
                if (!$studentExists) {
                    $masterStudent = $masterConn->table('students')->where('id', $studentId)->first();
                    if ($masterStudent) {
                         $localConn->table('students')->insert((array)$masterStudent);
                    } else {
                        continue; 
                    }
                }

                // === PURGE + MIRROR PATTERN (Master as Single Source of Truth) ===

                // Step 1: Build master history ID map for this student
                $masterHistoryIds = collect($data['histories_to_insert'])->pluck('id')->toArray();
                $masterHistoryIdMap = array_flip($masterHistoryIds);

                // Step 2: Find and purge local-only histories (not in master)
                $localHistoryIds = $localConn->table('saldo_histories')
                    ->where('student_id', $studentId)
                    ->pluck('id')
                    ->toArray();

                $rogueIds = [];
                foreach ($localHistoryIds as $lid) {
                    if (!isset($masterHistoryIdMap[$lid])) {
                        $rogueIds[] = $lid;
                    }
                }

                if (!empty($rogueIds)) {
                    foreach (array_chunk($rogueIds, 500) as $rogueChunk) {
                        $localConn->table('saldo_histories')->whereIn('id', $rogueChunk)->delete();
                    }
                }

                // Step 3: Bulk upsert all master histories (preserve original balance_before & balance_after from master)
                $toInsert = [];
                foreach ($data['histories_to_insert'] as $history) {
                    $toInsert[] = (array) $history;
                }

                if (!empty($toInsert)) {
                    foreach (array_chunk($toInsert, 100) as $batch) {
                        $localConn->table('saldo_histories')->upsert(
                            $batch,
                            ['id'],
                            ['student_id', 'type', 'amount', 'usage', 'description', 'status', 'balance_before', 'balance_after', 'created_at', 'updated_at', 'deleted_at']
                        );
                    }
                }

                // Step 4: Overwrite student saldo directly from master (Single Source of Truth)
                $masterStudent = $masterConn->table('students')->where('id', $studentId)->first();
                if ($masterStudent) {
                    $localConn->table('students')
                        ->where('id', $studentId)
                        ->update([
                            'saldo' => $masterStudent->saldo,
                            'saving' => $masterStudent->saving,
                            'updated_at' => now(),
                        ]);
                }

                // Step 5: Sync POS transactions if any
                foreach ($data['histories_to_insert'] as $history) {
                    $historyArray = (array) $history;
                    if (isset($historyArray['usage']) && $historyArray['usage'] === 'POS') {
                        $this->syncPosTransaction($historyArray['id']);
                    }
                }
                
                $processedCount++;
            }

            $localConn->table('database_sync_logs')->insert([
                'status' => 'success',
                'started_at' => now(),
                'finished_at' => now(),
                'duration' => 0,
                'report' => json_encode(['synced_students' => $processedCount, 'type' => 'SSOT_SYNC']),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $localConn->commit();
            Cache::forget($previewId);

            return [
                'status' => true,
                'message' => "Berhasil menyinkronkan saldo untuk {$processedCount} siswa dari aplikasi lama.",
                'count' => $processedCount
            ];

        } catch (\Throwable $e) {
            $localConn->rollBack();
            Log::error("Advanced Sync Execution Failed: " . $e->getMessage());
            
            return [
                'status' => false,
                'message' => "Gagal mengeksekusi sinkronisasi: " . $e->getMessage()
            ];
        }
    }

    private function syncPosTransaction($saldoHistoryId)
    {
        $localConn = DB::connection();
        $masterConn = $this->getRemoteConnection();

        $posTxMaster = $masterConn->table('point_of_sale_transactions')
            ->where('saldo_history_id', $saldoHistoryId)
            ->first();
        if (!$posTxMaster) {
            $posTxMaster = DB::connection('mysql_master')->table('point_of_sale_transactions')
                ->where('saldo_history_id', $saldoHistoryId)
                ->first();
        }

        if ($posTxMaster) {
            $exists = $localConn->table('point_of_sale_transactions')
                ->where('id', $posTxMaster->id)
                ->exists();

            if (!$exists) {
                // Check outlet
                if (!empty($posTxMaster->outlet_id)) {
                    $outletExists = $localConn->table('outlets')->where('id', $posTxMaster->outlet_id)->exists();
                    if (!$outletExists) {
                        $masterOutlet = $masterConn->table('outlets')->where('id', $posTxMaster->outlet_id)->first();
                        if ($masterOutlet) {
                            $localConn->table('outlets')->insert((array)$masterOutlet);
                        }
                    }
                }

                $localConn->table('point_of_sale_transactions')->insert((array)$posTxMaster);

                $detailsMaster = $masterConn->table('point_of_sale_transaction_details')
                    ->where('point_of_sale_transaction_id', $posTxMaster->id)
                    ->get();
                
                foreach ($detailsMaster as $detail) {
                    $dArr = (array) $detail;
                    $dId = $dArr['id'];
                    $dExists = $localConn->table('point_of_sale_transaction_details')->where('id', $dId)->exists();
                    if ($dExists) {
                        unset($dArr['id']);
                        $localConn->table('point_of_sale_transaction_details')->where('id', $dId)->update($dArr);
                    } else {
                        $localConn->table('point_of_sale_transaction_details')->insert($dArr);
                    }
                }
            }
        }
    }

    /**
     * Resolve the remote database connection (mysql_master / cahayatasbihdb as primary source of truth for old app,
     * fallback to mysql_aplikasi if unreachable).
     */
    private function getRemoteConnection()
    {
        try {
            DB::connection('mysql_master')->getPdo();
            return DB::connection('mysql_master');
        } catch (\Throwable $e) {
            return DB::connection('mysql_aplikasi');
        }
    }
}

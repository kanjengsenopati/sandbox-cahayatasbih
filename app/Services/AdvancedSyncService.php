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

        // Fetch Local Saldo History IDs as an O(1) hash map to avoid RAM exhaustion
        $localHistoryIdsMap = [];
        foreach (array_chunk($studentIds, 200) as $chunk) {
            $ids = $localConn->table('saldo_histories')
                ->whereIn('student_id', $chunk)
                ->whereNull('deleted_at')
                ->pluck('id')
                ->flip()
                ->toArray();
            $localHistoryIdsMap += $ids;
        }

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

            // Find histories in master that do NOT exist in local yet using O(1) hash map
            $newMasterHistories = $masterHistories->filter(function ($history) use ($localHistoryIdsMap) {
                return !isset($localHistoryIdsMap[$history->id]);
            });

            // Show student if they have ANY master histories matching filter OR if they have new master histories.
            // This prevents skipping students whose master transactions are already fully synced.
            if ($masterHistories->isEmpty() && $newMasterHistories->isEmpty()) {
                continue;
            }

            $currentLocalSaldo = $localStudent ? $localStudent->saldo : 0;
            
            // Calculate Accumulated Balance (Merging)
            $simulatedSaldo = $currentLocalSaldo;
            $newIn = 0;
            $newOut = 0;

            foreach ($newMasterHistories as $history) {
                if (in_array($history->type, ['IN', 'UNBLOCKED'])) {
                    $simulatedSaldo += $history->amount;
                    $newIn += $history->amount;
                } elseif (in_array($history->type, ['OUT', 'WITHDRAW', 'BLOCKED'])) {
                    $simulatedSaldo -= $history->amount;
                    $newOut += $history->amount;
                }
            }

            $conflictStatus = 'OK';
            if ($localStudent && $localStudent->saldo != $masterStudent->saldo) {
                $conflictStatus = 'CONFLICT_DETECTED';
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
                'master_saldo' => $masterStudent->saldo,
                'master_saldo_date' => $masterSaldoDate,
                'master_saldo_time' => $masterSaldoTime,
                'simulated_saldo' => $simulatedSaldo,
                'new_histories_count' => $masterHistories->count(), // Display TOTAL master histories in this period
                'total_in_added' => $newIn,
                'total_out_added' => $newOut,
                'conflict_status' => $conflictStatus,
                'histories_to_insert' => $masterHistories->values()->toArray() // Pass ALL master histories so executeSync can upsert them
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
     * Execute the actual merge into the local database
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

                // Insert the new histories
                foreach ($data['histories_to_insert'] as $history) {
                    $historyArray = (array) $history;
                    
                    // Merging logic: Recalculate ledger based on local running balance
                    $currentStudent = $localConn->table('students')->where('id', $studentId)->lockForUpdate()->first();
                    $balanceBefore = $currentStudent ? $currentStudent->saldo : 0;
                    $amount = $historyArray['amount'];
                    $type = $historyArray['type'];
                    $balanceAfter = $balanceBefore;
                    
                    $hId = $historyArray['id'];
                    $exists = $localConn->table('saldo_histories')->where('id', $hId)->exists();
                    
                    if ($exists) {
                        // If it already exists locally, just update it. Do NOT add amount to running balance to prevent double counting.
                        unset($historyArray['id']);
                        $localConn->table('saldo_histories')->where('id', $hId)->update($historyArray);
                    } else {
                        // If it's a new history, calculate the new running balance and insert.
                        if (in_array($type, ['IN', 'UNBLOCKED'])) {
                            $balanceAfter = $balanceBefore + $amount;
                        } elseif (in_array($type, ['OUT', 'WITHDRAW', 'BLOCKED'])) {
                            $balanceAfter = $balanceBefore - $amount;
                        }

                        $historyArray['balance_before'] = $balanceBefore;
                        $historyArray['balance_after'] = $balanceAfter;

                        $localConn->table('saldo_histories')->insert($historyArray);
                        
                        // Update student saldo only if it was a new history
                        $localConn->table('students')
                            ->where('id', $studentId)
                            ->update(['saldo' => $balanceAfter, 'updated_at' => now()]);
                    }

                    // If usage is POS, pull pos transaction
                    if (isset($historyArray['usage']) && $historyArray['usage'] === 'POS') {
                        $this->syncPosTransaction($hId);
                    }
                }
                
                $processedCount++;
            }

            $localConn->table('database_sync_logs')->insert([
                'status' => 'success',
                'started_at' => now(),
                'finished_at' => now(),
                'duration' => 0,
                'report' => json_encode(['merged_students' => $processedCount, 'type' => 'ADVANCED_MERGE']),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $localConn->commit();
            Cache::forget($previewId);

            return [
                'status' => true,
                'message' => "Berhasil menggabungkan (merge) data mutasi untuk {$processedCount} siswa.",
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

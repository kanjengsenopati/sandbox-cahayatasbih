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
        $previewId = 'sync_preview_' . (string) Str::uuid();
        
        $masterQuery = DB::connection('mysql_master')->table('students')
            ->select('students.id', 'students.name', 'students.nis', 'students.saldo', 'students.saving', 'classrooms.name as classroom_name', 'schools.name as school_name')
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
            $masterQuery->where(function($q) use ($filters) {
                $q->where('students.name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('students.nis', 'like', '%' . $filters['search'] . '%');
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
        $studentsLocal = DB::connection('mysql')->table('students')
            ->whereIn('id', $studentIds)
            ->get()->keyBy('id');

        // Fetch Master Saldo Histories based on date range
        $saldoHistoriesMasterQuery = DB::connection('mysql_master')->table('saldo_histories')
            ->whereIn('student_id', $studentIds)
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'asc'); // Must be chronological
        
        if (!empty($filters['start_date'])) {
            $saldoHistoriesMasterQuery->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $saldoHistoriesMasterQuery->whereDate('created_at', '<=', $filters['end_date']);
        }
        
        $saldoHistoriesMaster = $saldoHistoriesMasterQuery->get()->groupBy('student_id');

        // Fetch Local Saldo Histories for comparison
        $saldoHistoriesLocal = DB::connection('mysql')->table('saldo_histories')
            ->whereIn('student_id', $studentIds)
            ->whereNull('deleted_at')
            ->get()->groupBy('student_id');

        $previewData = [];

        foreach ($studentsMaster as $studentId => $masterStudent) {
            $localStudent = $studentsLocal->get($studentId);
            $masterHistories = $saldoHistoriesMaster->get($studentId) ?? collect();
            $localHistories = $saldoHistoriesLocal->get($studentId) ?? collect();

            $localHistoryIds = $localHistories->pluck('id')->toArray();
            
            // Find histories in master that do NOT exist in local yet
            $newMasterHistories = $masterHistories->filter(function ($history) use ($localHistoryIds) {
                return !in_array($history->id, $localHistoryIds);
            });

            if ($newMasterHistories->isEmpty() && $localStudent) {
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

            $previewData[] = [
                'student_id' => $studentId,
                'nis' => $masterStudent->nis ?? '-',
                'name' => $masterStudent->name,
                'classroom' => $masterStudent->classroom_name ?? '-',
                'school' => $masterStudent->school_name ?? '-',
                'current_local_saldo' => $currentLocalSaldo,
                'master_saldo' => $masterStudent->saldo,
                'simulated_saldo' => $simulatedSaldo,
                'new_histories_count' => $newMasterHistories->count(),
                'total_in_added' => $newIn,
                'total_out_added' => $newOut,
                'conflict_status' => $conflictStatus,
                'histories_to_insert' => $newMasterHistories->toArray()
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
        $previewData = Cache::get($previewId);

        if (!$previewData) {
            throw new \Exception("Data preview tidak ditemukan atau sudah kadaluarsa. Silakan ulangi filter preview.");
        }

        DB::connection('mysql')->beginTransaction();

        try {
            $processedCount = 0;

            foreach ($previewData as $data) {
                $studentId = $data['student_id'];
                
                // If specific students are selected, skip those not in the list
                if ($selectedStudentIds !== null && !in_array((string)$studentId, $selectedStudentIds)) {
                    continue;
                }
                
                // Ensure student exists locally
                $studentExists = DB::connection('mysql')->table('students')->where('id', $studentId)->exists();
                if (!$studentExists) {
                    $masterStudent = DB::connection('mysql_master')->table('students')->where('id', $studentId)->first();
                    if ($masterStudent) {
                         DB::connection('mysql')->table('students')->insert((array)$masterStudent);
                    } else {
                        continue; 
                    }
                }

                // Insert the new histories
                foreach ($data['histories_to_insert'] as $history) {
                    $historyArray = (array) $history;
                    
                    // Merging logic: Recalculate ledger based on local running balance
                    $currentStudent = DB::connection('mysql')->table('students')->where('id', $studentId)->lockForUpdate()->first();
                    $balanceBefore = $currentStudent->saldo;
                    $amount = $historyArray['amount'];
                    $type = $historyArray['type'];
                    $balanceAfter = $balanceBefore;

                    if (in_array($type, ['IN', 'UNBLOCKED'])) {
                        $balanceAfter = $balanceBefore + $amount;
                    } elseif (in_array($type, ['OUT', 'WITHDRAW', 'BLOCKED'])) {
                        $balanceAfter = $balanceBefore - $amount;
                    }

                    $historyArray['balance_before'] = $balanceBefore;
                    $historyArray['balance_after'] = $balanceAfter;
                    
                    DB::connection('mysql')->table('saldo_histories')->insert($historyArray);

                    // If usage is POS, pull pos transaction
                    if ($historyArray['usage'] === 'POS') {
                        $this->syncPosTransaction($historyArray['id']);
                    }

                    // Update student saldo
                    DB::connection('mysql')->table('students')
                        ->where('id', $studentId)
                        ->update(['saldo' => $balanceAfter, 'updated_at' => now()]);
                }
                
                $processedCount++;
            }

            DB::connection('mysql')->table('database_sync_logs')->insert([
                'status' => 'success',
                'started_at' => now(),
                'finished_at' => now(),
                'duration' => 0,
                'report' => json_encode(['merged_students' => $processedCount, 'type' => 'ADVANCED_MERGE']),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::connection('mysql')->commit();
            Cache::forget($previewId);

            return [
                'status' => true,
                'message' => "Berhasil menggabungkan (merge) data mutasi untuk {$processedCount} siswa.",
                'count' => $processedCount
            ];

        } catch (\Exception $e) {
            DB::connection('mysql')->rollBack();
            Log::error("Advanced Sync Execution Failed: " . $e->getMessage());
            
            return [
                'status' => false,
                'message' => "Gagal mengeksekusi sinkronisasi: " . $e->getMessage()
            ];
        }
    }

    private function syncPosTransaction($saldoHistoryId)
    {
        $posTxMaster = DB::connection('mysql_master')->table('point_of_sale_transactions')
            ->where('saldo_history_id', $saldoHistoryId)
            ->first();

        if ($posTxMaster) {
            $exists = DB::connection('mysql')->table('point_of_sale_transactions')
                ->where('id', $posTxMaster->id)
                ->exists();

            if (!$exists) {
                // Check outlet
                $outletExists = DB::connection('mysql')->table('outlets')->where('id', $posTxMaster->outlet_id)->exists();
                if (!$outletExists) {
                    $masterOutlet = DB::connection('mysql_master')->table('outlets')->where('id', $posTxMaster->outlet_id)->first();
                    if ($masterOutlet) {
                        DB::connection('mysql')->table('outlets')->insert((array)$masterOutlet);
                    }
                }

                DB::connection('mysql')->table('point_of_sale_transactions')->insert((array)$posTxMaster);

                $detailsMaster = DB::connection('mysql_master')->table('point_of_sale_transaction_details')
                    ->where('point_of_sales_transaction_id', $posTxMaster->id)
                    ->get();
                
                foreach ($detailsMaster as $detail) {
                    DB::connection('mysql')->table('point_of_sale_transaction_details')->updateOrInsert(
                        ['id' => $detail->id],
                        (array)$detail
                    );
                }
            }
        }
    }
}

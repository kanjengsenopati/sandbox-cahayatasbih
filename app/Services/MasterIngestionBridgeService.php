<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MasterIngestionBridgeService
{
    /**
     * Analyze and calculate diff matrix between Master DB (mysql_master) and Local Staging DB.
     *
     * @param string $module
     * @param int $limit
     * @return array
     */
    public function analyzeModuleDiff(string $module = 'students', int $limit = 50, ?string $schoolId = null, ?string $classroomId = null, ?string $academicYearId = null, ?string $billTypeId = null): array
    {
        $localConn = DB::connection();
        $masterConn = DB::connection('mysql_master');

        $result = [
            'module' => $module,
            'status_summary' => [
                'new_count' => 0,
                'update_count' => 0,
                'match_count' => 0,
                'conflict_count' => 0,
                'total_analyzed' => 0,
            ],
            'items' => [],
        ];

        try {
            switch ($module) {
                case 'schools':
                    $this->analyzeSchools($masterConn, $localConn, $result, $limit);
                    break;
                case 'classrooms':
                    $this->analyzeClassrooms($masterConn, $localConn, $result, $limit);
                    break;
                case 'academic_years':
                    $this->analyzeAcademicYears($masterConn, $localConn, $result, $limit);
                    break;
                case 'bill_types':
                    $this->analyzeBillTypes($masterConn, $localConn, $result, $limit);
                    break;
                case 'saldo':
                    $this->analyzeSaldo($masterConn, $localConn, $result, $limit);
                    break;
                case 'billing_status':
                    $this->analyzeBillingStatus($masterConn, $localConn, $result, $limit, $schoolId, $classroomId, $academicYearId, $billTypeId);
                    break;
                case 'students':
                default:
                    $this->analyzeStudents($masterConn, $localConn, $result, $limit);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error("[MasterIngestionBridge] Analysis error for module {$module}: " . $e->getMessage());
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    private function analyzeStudents($masterConn, $localConn, array &$result, int $limit)
    {
        $query = $masterConn->table('students')
            ->whereNull('deleted_at')
            ->latest('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $masterRecords = $query->get();

        $localStudentIds = $localConn->table('students')->pluck('id')->keyBy(fn($id) => (string)$id);
        $localClassroomIds = $localConn->table('classrooms')->pluck('id')->keyBy(fn($id) => (string)$id);

        $masterClassrooms = $masterConn->table('classrooms')->pluck('name', 'id')->toArray();
        $localClassrooms = $localConn->table('classrooms')->pluck('name', 'id')->toArray();

        foreach ($masterRecords as $mRec) {
            $result['status_summary']['total_analyzed']++;
            $idStr = (string)$mRec->id;
            $localRec = $localConn->table('students')->where('id', $mRec->id)->first();

            $status = 'EXACT_MATCH';
            $diffs = [];

            if (!$localRec) {
                $status = 'NEW_RECORD';
                $result['status_summary']['new_count']++;
            } else {
                if ($localRec->name !== $mRec->name) {
                    $diffs['Nama Siswa'] = ['master' => $mRec->name, 'local' => $localRec->name];
                }
                if ($localRec->nis !== $mRec->nis) {
                    $diffs['NIS'] = ['master' => $mRec->nis ?: '(Kosong)', 'local' => $localRec->nis ?: '(Kosong)'];
                }
                if ($localRec->classroom_id !== $mRec->classroom_id) {
                    $mClassName = $masterClassrooms[$mRec->classroom_id] ?? ('ID: ' . Str::limit($mRec->classroom_id, 8, ''));
                    $lClassName = $localClassrooms[$localRec->classroom_id] ?? ('ID: ' . Str::limit($localRec->classroom_id, 8, ''));

                    $diffs['Kelas (classroom_id)'] = [
                        'master' => "{$mClassName} [" . Str::limit($mRec->classroom_id, 8, '') . "]",
                        'local' => "{$lClassName} [" . Str::limit($localRec->classroom_id, 8, '') . "]"
                    ];
                }
                if ($localRec->status !== $mRec->status) {
                    $diffs['Status Siswa'] = ['master' => $mRec->status, 'local' => $localRec->status];
                }

                if (!empty($diffs)) {
                    $status = 'UPDATE_REQUIRED';
                    $result['status_summary']['update_count']++;
                } else {
                    $result['status_summary']['match_count']++;
                }
            }

            // Check relational integrity conflict (missing classroom locally)
            if (!empty($mRec->classroom_id) && !$localClassroomIds->has((string)$mRec->classroom_id)) {
                $status = 'CONFLICT';
                $mClassName = $masterClassrooms[$mRec->classroom_id] ?? $mRec->classroom_id;
                $diffs['conflict'] = "Kelas Aplikasi Lama '{$mClassName}' (ID: " . Str::limit($mRec->classroom_id, 8, '') . ") belum ada di Database Lokal";
                $result['status_summary']['conflict_count']++;
                if ($localRec && isset($result['status_summary']['update_count'])) {
                    $result['status_summary']['update_count']--;
                }
            }

            $result['items'][] = [
                'id' => $mRec->id,
                'code_or_nis' => $mRec->nis ?: Str::limit($mRec->id, 8, ''),
                'name' => $mRec->name,
                'status' => $status,
                'diffs' => $diffs,
                'raw_master' => (array) $mRec,
            ];
        }
    }

    private function analyzeClassrooms($masterConn, $localConn, array &$result, int $limit)
    {
        $masterRecords = $masterConn->table('classrooms')
            ->whereNull('deleted_at')
            ->limit($limit)
            ->get();

        foreach ($masterRecords as $mRec) {
            $result['status_summary']['total_analyzed']++;
            $localRec = $localConn->table('classrooms')->where('id', $mRec->id)->first();

            $status = 'EXACT_MATCH';
            $diffs = [];

            if (!$localRec) {
                $status = 'NEW_RECORD';
                $result['status_summary']['new_count']++;
            } else {
                if ($localRec->name !== $mRec->name) {
                    $diffs['name'] = ['master' => $mRec->name, 'local' => $localRec->name];
                }
                if (!empty($diffs)) {
                    $status = 'UPDATE_REQUIRED';
                    $result['status_summary']['update_count']++;
                } else {
                    $result['status_summary']['match_count']++;
                }
            }

            $result['items'][] = [
                'id' => $mRec->id,
                'code_or_nis' => $mRec->id,
                'name' => $mRec->name,
                'status' => $status,
                'diffs' => $diffs,
                'raw_master' => (array) $mRec,
            ];
        }
    }

    private function analyzeSchools($masterConn, $localConn, array &$result, int $limit)
    {
        $masterRecords = $masterConn->table('schools')
            ->whereNull('deleted_at')
            ->limit($limit)
            ->get();

        foreach ($masterRecords as $mRec) {
            $result['status_summary']['total_analyzed']++;
            $localRec = $localConn->table('schools')->where('id', $mRec->id)->first();

            $status = 'EXACT_MATCH';
            $diffs = [];

            if (!$localRec) {
                $status = 'NEW_RECORD';
                $result['status_summary']['new_count']++;
            } else {
                if ($localRec->name !== $mRec->name) {
                    $diffs['name'] = ['master' => $mRec->name, 'local' => $localRec->name];
                }
                if (!empty($diffs)) {
                    $status = 'UPDATE_REQUIRED';
                    $result['status_summary']['update_count']++;
                } else {
                    $result['status_summary']['match_count']++;
                }
            }

            $result['items'][] = [
                'id' => $mRec->id,
                'code_or_nis' => $mRec->id,
                'name' => $mRec->name,
                'status' => $status,
                'diffs' => $diffs,
                'raw_master' => (array) $mRec,
            ];
        }
    }

    private function analyzeAcademicYears($masterConn, $localConn, array &$result, int $limit)
    {
        $masterRecords = $masterConn->table('academic_years')
            ->whereNull('deleted_at')
            ->limit($limit)
            ->get();

        foreach ($masterRecords as $mRec) {
            $result['status_summary']['total_analyzed']++;
            $localRec = $localConn->table('academic_years')->where('id', $mRec->id)->first();

            $status = 'EXACT_MATCH';
            $diffs = [];

            if (!$localRec) {
                $status = 'NEW_RECORD';
                $result['status_summary']['new_count']++;
            } else {
                if ($localRec->name !== $mRec->name) {
                    $diffs['name'] = ['master' => $mRec->name, 'local' => $localRec->name];
                }
                if (!empty($diffs)) {
                    $status = 'UPDATE_REQUIRED';
                    $result['status_summary']['update_count']++;
                } else {
                    $result['status_summary']['match_count']++;
                }
            }

            $result['items'][] = [
                'id' => $mRec->id,
                'code_or_nis' => $mRec->id,
                'name' => $mRec->name,
                'status' => $status,
                'diffs' => $diffs,
                'raw_master' => (array) $mRec,
            ];
        }
    }

    private function analyzeBillTypes($masterConn, $localConn, array &$result, int $limit)
    {
        $query = $masterConn->table('bill_types')
            ->whereNull('deleted_at');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $masterRecords = $query->get();

        $masterItems = $masterConn->table('bill_items')->pluck('name', 'id')->toArray();
        $localItems = $localConn->table('bill_items')->pluck('name', 'id')->toArray();

        $masterYears = $masterConn->table('academic_years')->pluck('name', 'id')->toArray();
        $localYears = $localConn->table('academic_years')->pluck('name', 'id')->toArray();

        foreach ($masterRecords as $mRec) {
            $result['status_summary']['total_analyzed']++;
            $localRec = $localConn->table('bill_types')->where('id', $mRec->id)->first();

            $posName = $masterItems[$mRec->bill_item_id] ?? ($localItems[$mRec->bill_item_id] ?? '');
            $posPrefix = $posName ? "[{$posName}] " : "";

            $status = 'EXACT_MATCH';
            $diffs = [];

            if (!$localRec) {
                $status = 'NEW_RECORD';
                $result['status_summary']['new_count']++;
            } else {
                if ($localRec->name !== $mRec->name) {
                    $diffs['Nama Jenis Tagihan'] = [
                        'master' => $posPrefix . $mRec->name,
                        'local' => $posPrefix . $localRec->name
                    ];
                }
                if (isset($mRec->bill_item_id) && isset($localRec->bill_item_id) && $localRec->bill_item_id !== $mRec->bill_item_id) {
                    $mItemName = $masterItems[$mRec->bill_item_id] ?? ('ID: ' . Str::limit($mRec->bill_item_id, 8, ''));
                    $lItemName = $localItems[$localRec->bill_item_id] ?? ('ID: ' . Str::limit($localRec->bill_item_id, 8, ''));
                    $diffs['Pos Pembayaran (bill_item_id)'] = [
                        'master' => $mItemName,
                        'local' => $lItemName
                    ];
                }
                if (isset($mRec->academic_year_id) && isset($localRec->academic_year_id) && $localRec->academic_year_id !== $mRec->academic_year_id) {
                    $mYear = $masterYears[$mRec->academic_year_id] ?? ('ID: ' . Str::limit($mRec->academic_year_id, 8, ''));
                    $lYear = $localYears[$localRec->academic_year_id] ?? ('ID: ' . Str::limit($localRec->academic_year_id, 8, ''));
                    $diffs['Tahun Ajaran (academic_year_id)'] = [
                        'master' => "{$mYear} [" . Str::limit($mRec->academic_year_id, 8, '') . "]",
                        'local' => "{$lYear} [" . Str::limit($localRec->academic_year_id, 8, '') . "]"
                    ];
                }

                if (!empty($diffs)) {
                    $status = 'UPDATE_REQUIRED';
                    $result['status_summary']['update_count']++;
                } else {
                    $result['status_summary']['match_count']++;
                }
            }

            $result['items'][] = [
                'id' => $mRec->id,
                'code_or_nis' => Str::limit($mRec->id, 8, ''),
                'name' => $posPrefix . $mRec->name,
                'status' => $status,
                'diffs' => $diffs,
                'raw_master' => (array) $mRec,
            ];
        }
    }

    private function analyzeSaldo($masterConn, $localConn, array &$result, int $limit)
    {
        $query = $masterConn->table('students')
            ->select('id', 'name', 'nis', 'saldo')
            ->whereNull('deleted_at')
            ->latest('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $masterRecords = $query->get();

        foreach ($masterRecords as $mRec) {
            $result['status_summary']['total_analyzed']++;
            $localRec = $localConn->table('students')->where('id', $mRec->id)->first();

            $status = 'EXACT_MATCH';
            $diffs = [];
            
            // Resolve real-time active balance from Master DB's `saldo_histories` ledger (fixes legacy master cache column bugs)
            $latestMasterHistory = $masterConn->table('saldo_histories')
                ->where('student_id', $mRec->id)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $columnSaldo = (int) ($mRec->saldo ?? 0);
            $masterSaldo = ($latestMasterHistory && isset($latestMasterHistory->balance_after) && $latestMasterHistory->balance_after !== null) 
                ? (int) $latestMasterHistory->balance_after 
                : $columnSaldo;

            // Count POS transaction history records in Master vs Local DB
            $masterHistCount = $masterConn->table('saldo_histories')->where('student_id', $mRec->id)->count();
            $localHistCount = $localRec ? $localConn->table('saldo_histories')->where('student_id', $mRec->id)->count() : 0;

            if (!$localRec) {
                $status = 'NEW_RECORD';
                $result['status_summary']['new_count']++;
                $diffs['saldo'] = [
                    'master' => 'Rp ' . number_format($masterSaldo, 0, ',', '.') . " ({$masterHistCount} Riwayat Tx)",
                    'local' => 'Belum Ada Record'
                ];
            } else {
                $localSaldo = (int) ($localRec->saldo ?? 0);
                if ($localSaldo !== $masterSaldo || $localHistCount < $masterHistCount) {
                    $diffs['saldo'] = [
                        'master' => 'Rp ' . number_format($masterSaldo, 0, ',', '.') . " ({$masterHistCount} Riwayat Tx)",
                        'local' => 'Rp ' . number_format($localSaldo, 0, ',', '.') . " ({$localHistCount} Riwayat Tx)"
                    ];
                    $status = 'UPDATE_REQUIRED';
                    $result['status_summary']['update_count']++;
                } else {
                    $result['status_summary']['match_count']++;
                }
            }

            $rawMaster = (array) $mRec;
            $rawMaster['saldo'] = $masterSaldo;

            $result['items'][] = [
                'id' => $mRec->id,
                'code_or_nis' => $mRec->nis ?? $mRec->id,
                'name' => $mRec->name,
                'status' => $status,
                'diffs' => $diffs,
                'raw_master' => $rawMaster,
            ];
        }
    }

    private function analyzeBillingStatus($masterConn, $localConn, array &$result, int $limit, ?string $schoolId = null, ?string $classroomId = null, ?string $academicYearId = null, ?string $billTypeId = null)
    {
        $masterClassrooms = $masterConn->table('classrooms')->pluck('name', 'id')->toArray();
        $masterSchools = $masterConn->table('schools')->pluck('name', 'id')->toArray();
        $classroomSchoolMap = $masterConn->table('classrooms')->pluck('school_id', 'id')->toArray();

        $classroomsQuery = $masterConn->table('classrooms')->whereNull('deleted_at');
        if (!empty($schoolId)) {
            $classroomsQuery->where('school_id', $schoolId);
        }
        $targetClassroomIds = $classroomsQuery->pluck('id')->map(fn($id) => (string)$id)->toArray();

        $query = $masterConn->table('students')
            ->select('id', 'name', 'nis', 'classroom_id')
            ->whereNull('deleted_at');

        if (!empty($classroomId)) {
            // STRICT CONSTRAINT: Relational cross-check
            if (!empty($schoolId) && !in_array($classroomId, $targetClassroomIds)) {
                $query->where('id', 'STRICT_CONSTRAINT_VIOLATION_FORCE_EMPTY');
            } else {
                $query->where('classroom_id', $classroomId);
            }
        } elseif (!empty($schoolId)) {
            $query->whereIn('classroom_id', $targetClassroomIds);
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $masterStudents = $query->get();
        $studentIds = $masterStudents->pluck('id')->toArray();

        // 1. Bulk query for Master DB bills summary
        $masterBillsGroup = [];
        if (!empty($studentIds)) {
            $mQuery = $masterConn->table('bills')
                ->whereIn('student_id', $studentIds)
                ->whereNull('deleted_at');
            
            if (!empty($academicYearId)) {
                $mQuery->where('academic_year_id', $academicYearId);
            }
            if (!empty($billTypeId)) {
                $mQuery->where('bill_type_id', $billTypeId);
            }

            $mRows = $mQuery->select('student_id', 'status', DB::raw('COUNT(*) as total_cnt'), DB::raw('SUM(amount) as total_amt'), DB::raw('GROUP_CONCAT(month) as months'))
                ->groupBy('student_id', 'status')
                ->get();

            foreach ($mRows as $r) {
                $sid = (string) $r->student_id;
                if (!isset($masterBillsGroup[$sid])) {
                    $masterBillsGroup[$sid] = ['total_cnt' => 0, 'paid_cnt' => 0, 'paid_amt' => 0, 'paid_months' => [], 'unpaid_months' => []];
                }
                $masterBillsGroup[$sid]['total_cnt'] += (int) $r->total_cnt;
                
                $monthsArr = array_filter(explode(',', (string)$r->months));
                if ($r->status === 'PAID') {
                    $masterBillsGroup[$sid]['paid_cnt'] += (int) $r->total_cnt;
                    $masterBillsGroup[$sid]['paid_amt'] += (int) $r->total_amt;
                    $masterBillsGroup[$sid]['paid_months'] = array_merge($masterBillsGroup[$sid]['paid_months'], $monthsArr);
                } else {
                    $masterBillsGroup[$sid]['unpaid_months'] = array_merge($masterBillsGroup[$sid]['unpaid_months'], $monthsArr);
                }
            }
        }

        // Smart UUID Mapping for Local Query
        $localBillTypeId = $billTypeId;
        if (!empty($billTypeId)) {
            $masterBillTypeForAnalyze = $masterConn->table('bill_types')->where('id', $billTypeId)->first();
            if ($masterBillTypeForAnalyze) {
                $masterCleanName = str_replace(' ', '', $masterBillTypeForAnalyze->name);
                $localBillTypeForAnalyze = $localConn->table('bill_types')
                    ->whereRaw("REPLACE(name, ' ', '') = ?", [$masterCleanName])
                    ->where('academic_year_id', $masterBillTypeForAnalyze->academic_year_id)
                    ->first();
                if ($localBillTypeForAnalyze) {
                    $localBillTypeId = $localBillTypeForAnalyze->id;
                }
            }
        }

        // 2. Bulk query for Local DB bills summary
        $localBillsGroup = [];
        if (!empty($studentIds)) {
            $lQuery = $localConn->table('bills')
                ->whereIn('student_id', $studentIds)
                ->whereNull('deleted_at');

            if (!empty($academicYearId)) {
                $lQuery->where('academic_year_id', $academicYearId);
            }
            if (!empty($localBillTypeId)) {
                $lQuery->where('bill_type_id', $localBillTypeId);
            }

            $lRows = $lQuery->select('student_id', 'status', DB::raw('COUNT(*) as total_cnt'), DB::raw('SUM(amount) as total_amt'), DB::raw('GROUP_CONCAT(month) as months'))
                ->groupBy('student_id', 'status')
                ->get();

            foreach ($lRows as $r) {
                $sid = (string) $r->student_id;
                if (!isset($localBillsGroup[$sid])) {
                    $localBillsGroup[$sid] = ['total_cnt' => 0, 'paid_cnt' => 0, 'paid_amt' => 0, 'paid_months' => [], 'unpaid_months' => []];
                }
                $localBillsGroup[$sid]['total_cnt'] += (int) $r->total_cnt;
                
                $monthsArr = array_filter(explode(',', (string)$r->months));
                if ($r->status === 'PAID') {
                    $localBillsGroup[$sid]['paid_cnt'] += (int) $r->total_cnt;
                    $localBillsGroup[$sid]['paid_amt'] += (int) $r->total_amt;
                    $localBillsGroup[$sid]['paid_months'] = array_merge($localBillsGroup[$sid]['paid_months'], $monthsArr);
                } else {
                    $localBillsGroup[$sid]['unpaid_months'] = array_merge($localBillsGroup[$sid]['unpaid_months'], $monthsArr);
                }
            }
        }

        foreach ($masterStudents as $mRec) {
            $result['status_summary']['total_analyzed']++;

            $cId = (string) $mRec->classroom_id;
            $sId = (string) ($classroomSchoolMap[$cId] ?? '');
            $className = $masterClassrooms[$cId] ?? 'Non-Kelas';
            $schoolName = $masterSchools[$sId] ?? 'Non-Sekolah';

            $localRec = $localConn->table('students')->where('id', $mRec->id)->first();

            $sidStr = (string) $mRec->id;
            $mSum = $masterBillsGroup[$sidStr] ?? ['total_cnt' => 0, 'paid_cnt' => 0, 'paid_amt' => 0];
            $lSum = $localBillsGroup[$sidStr] ?? ['total_cnt' => 0, 'paid_cnt' => 0, 'paid_amt' => 0];

            $mBillsCount = $mSum['total_cnt'];
            $mPaidCount = $mSum['paid_cnt'];
            $mPaidAmount = $mSum['paid_amt'];

            $lBillsCount = $lSum['total_cnt'];
            $lPaidCount = $lSum['paid_cnt'];
            $lPaidAmount = $lSum['paid_amt'];

            // STRICT CONSTRAINT: No Zero-Bill Leakage
            // Jangan tampilkan siswa yang sama sekali tidak memiliki tagihan terkait filter ini
            if ($mBillsCount === 0 && $lBillsCount === 0) {
                // Adjust analyzed count since we skip it from being rendered
                $result['status_summary']['total_analyzed']--;
                continue;
            }

            $status = 'EXACT_MATCH';
            $diffs = [];

            $diffInfo = [
                'type' => 'monthly_cards',
                'master' => [
                    'paid_cnt' => $mPaidCount,
                    'total_cnt' => $mBillsCount,
                    'paid_amt' => $mPaidAmount,
                    'paid_months' => array_values(array_unique($mSum['paid_months'] ?? [])),
                    'unpaid_months' => array_values(array_unique($mSum['unpaid_months'] ?? []))
                ],
                'local' => [
                    'paid_cnt' => $lPaidCount,
                    'total_cnt' => $lBillsCount,
                    'paid_amt' => $lPaidAmount,
                    'paid_months' => array_values(array_unique($lSum['paid_months'] ?? [])),
                    'unpaid_months' => array_values(array_unique($lSum['unpaid_months'] ?? []))
                ]
            ];

            if (!$localRec) {
                $status = 'NEW_RECORD';
                $result['status_summary']['new_count']++;
                $diffInfo['local']['is_empty'] = true;
                $diffs['Status Tagihan Siswa'] = $diffInfo;
            } else {
                if ($mPaidCount !== $lPaidCount || $mBillsCount !== $lBillsCount || $mPaidAmount !== $lPaidAmount) {
                    $status = 'UPDATE_REQUIRED';
                    $result['status_summary']['update_count']++;
                    $diffs['Status Tagihan Siswa'] = $diffInfo;
                } else {
                    $result['status_summary']['match_count']++;
                }
            }

            $result['items'][] = [
                'id' => $mRec->id,
                'code_or_nis' => $mRec->nis ?: Str::limit($mRec->id, 8, ''),
                'name' => "{$mRec->name} [{$schoolName} - {$className}]",
                'status' => $status,
                'diffs' => $diffs,
                'raw_master' => (array) $mRec,
            ];
        }
    }

    /**
     * Execute verified merge for selected record IDs.
     *
     * @param string $module
     * @param array $selectedIds
     * @return array
     */
    public function executeVerifiedMerge(string $module, array $selectedIds, ?string $academicYearId = null, ?string $billTypeId = null): array
    {
        if (empty($selectedIds)) {
            return ['status' => 'error', 'message' => 'Tidak ada record yang dipilih untuk digabungkan.'];
        }

        $masterConn = DB::connection('mysql_master');
        $localConn = DB::connection();
        $syncedCount = 0;

        $driver = $localConn->getDriverName();

        try {
            $localConn->beginTransaction();
            if ($driver === 'sqlite') {
                $localConn->statement('PRAGMA foreign_keys = OFF;');
            } else {
                $localConn->statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            $targetTable = ($module === 'saldo' || $module === 'billing_status') ? 'students' : $module;

            $masterRecords = $masterConn->table($targetTable)
                ->whereIn('id', $selectedIds)
                ->get();

            foreach ($masterRecords as $mRec) {
                if ($module === 'billing_status') {
                    // Ingest / upsert bills for selected student IDs matching filters
                    $mQuery = $masterConn->table('bills')->where('student_id', $mRec->id)->whereNull('deleted_at');
                    if (!empty($academicYearId)) {
                        $mQuery->where('academic_year_id', $academicYearId);
                    }
                    if (!empty($billTypeId)) {
                        $mQuery->where('bill_type_id', $billTypeId);
                    }
                    // Handle duplicate bills in Master DB: prioritize PAID status
                    $rawMasterBills = $mQuery->get();
                    file_put_contents('debug_counts.log', "student: " . $mRec->id . " - rawMasterBills: " . count($rawMasterBills) . "
", FILE_APPEND);
                    $masterBills = [];
                    foreach ($rawMasterBills as $b) {
                        if (!isset($masterBills[$b->month])) {
                            $masterBills[$b->month] = $b;
                        } else {
                            if ($b->status === 'PAID') {
                                $masterBills[$b->month] = $b;
                            }
                        }
                    }

                    if (!empty($masterBills)) {
                        foreach ($masterBills as $mBill) {
                            $row = (array) $mBill;
                            $targetBillTypeId = $mBill->bill_type_id;

                            // Smart UUID Mapping
                            $masterBillType = $masterConn->table('bill_types')->where('id', $mBill->bill_type_id)->first();
                            if ($masterBillType) {
                                $masterCleanName = str_replace(' ', '', $masterBillType->name);
                                $localBillType = $localConn->table('bill_types')
                                    ->whereRaw("REPLACE(name, ' ', '') = ?", [$masterCleanName])
                                    ->where('academic_year_id', $masterBillType->academic_year_id)
                                    ->first();
                                if ($localBillType) {
                                    $targetBillTypeId = $localBillType->id;
                                }
                            }
                            $row['bill_type_id'] = $targetBillTypeId;

                            // Injeksi manual paid_amount karena database Master tidak memiliki field paid_amount
                            if (isset($row['status'])) {
                                if ($row['status'] === 'PAID') {
                                    $row['paid_amount'] = isset($row['amount']) ? $row['amount'] : 0;
                                } elseif ($row['status'] === 'UNPAID') {
                                    $row['paid_amount'] = 0;
                                }
                            }

                            // Temukan bill lokal berdasarkan student_id, mapped bill_type_id, dan month
                            $localBill = $localConn->table('bills')
                                ->where('student_id', $mBill->student_id)
                                ->where('bill_type_id', $targetBillTypeId)
                                ->where('month', $mBill->month)
                                ->first();

                            $targetBillId = $mBill->id;

                            if ($localBill) {
                                $targetBillId = $localBill->id;
                                unset($row['id']);
                                file_put_contents('debug_sync.log', json_encode($row) . PHP_EOL, FILE_APPEND);
                                $localConn->table('bills')->where('id', $targetBillId)->update($row);
                            } else {
                                $targetBillId = $row['id'];
                                $localConn->table('bills')->insert($row);
                            }

                            // Enforce Transactional Atomicity: Copy transactions when paid
                            if ($mBill->status === 'PAID') {
                                $mDetails = $masterConn->table('transaction_details')->where('bill_id', $mBill->id)->get();
                                foreach ($mDetails as $mDetail) {
                                    $mTx = $masterConn->table('transactions')->where('id', $mDetail->transaction_id)->first();
                                    
                                    if ($mTx && !$localConn->table('transactions')->where('id', $mTx->id)->exists()) {
                                        $localConn->table('transactions')->insert((array) $mTx);
                                        
                                        // Also ingest saldo history if paid via SALDO
                                        if ($mTx->payment_method_id) {
                                            try {
                                                // Attempt to find by description or amount since transaction_id is missing
                                                $mSaldoHist = $masterConn->table('saldo_histories')
                                                    ->where('student_id', $mTx->student_id)
                                                    ->where('amount', $mTx->amount)
                                                    ->where('created_at', '>=', $mTx->created_at)
                                                    ->get();
                                                foreach ($mSaldoHist as $sh) {
                                                    if (!$localConn->table('saldo_histories')->where('id', $sh->id)->exists()) {
                                                        $localConn->table('saldo_histories')->insert((array) $sh);
                                                    }
                                                }
                                            } catch (\Exception $e) {
                                                // Log securely, don't crash the bill update
                                                \Illuminate\Support\Facades\Log::warning("Could not sync saldo_histories for tx {$mTx->id}: " . $e->getMessage());
                                            }
                                        }
                                    }
                                    
                                    $detailRow = (array) $mDetail;
                                    $detailRow['bill_id'] = $targetBillId; // Re-map ke bill lokal
                                    
                                    if (!$localConn->table('transaction_details')->where('id', $detailRow['id'])->exists()) {
                                        $localConn->table('transaction_details')->insert($detailRow);
                                    }
                                }
                            }
                        }
                    }
                } elseif ($module === 'saldo') {
                    // Resolve real-time active balance from Master DB's `saldo_histories`
                    $latestMasterHistory = $masterConn->table('saldo_histories')
                        ->where('student_id', $mRec->id)
                        ->orderBy('created_at', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();

                    $targetSaldo = ($latestMasterHistory && isset($latestMasterHistory->balance_after) && $latestMasterHistory->balance_after !== null)
                        ? (int) $latestMasterHistory->balance_after
                        : (int) ($mRec->saldo ?? 0);

                    // 1. Update active balance in local students table
                    $localConn->table('students')->where('id', $mRec->id)->update([
                        'saldo' => $targetSaldo,
                        'updated_at' => now(),
                    ]);

                    // 2. Incremental Ingestion of POS transaction history (saldo_histories)
                    $masterHistories = $masterConn->table('saldo_histories')
                        ->where('student_id', $mRec->id)
                        ->get();

                    if ($masterHistories->isNotEmpty()) {
                        foreach ($masterHistories as $mHist) {
                            $row = (array) $mHist;
                            $rId = $row['id'];
                            $exists = $localConn->table('saldo_histories')->where('id', $rId)->exists();
                            if ($exists) {
                                unset($row['id']);
                                $localConn->table('saldo_histories')->where('id', $rId)->update($row);
                            } else {
                                $localConn->table('saldo_histories')->insert($row);
                            }
                        }
                    }
                } else {
                    // If student, ensure classroom exists
                    if ($module === 'students' && !empty($mRec->classroom_id)) {
                        $mClass = $masterConn->table('classrooms')->where('id', $mRec->classroom_id)->first();
                        if ($mClass) {
                            $mClassArr = (array) $mClass;
                            $cId = $mClassArr['id'];
                            if ($localConn->table('classrooms')->where('id', $cId)->exists()) {
                                unset($mClassArr['id']);
                                $localConn->table('classrooms')->where('id', $cId)->update($mClassArr);
                            } else {
                                $localConn->table('classrooms')->insert($mClassArr);
                            }
                        }
                    }

                    $mRecArr = (array) $mRec;
                    $rId = $mRecArr['id'];
                    if ($localConn->table($module)->where('id', $rId)->exists()) {
                        unset($mRecArr['id']);
                        $localConn->table($module)->where('id', $rId)->update($mRecArr);
                    } else {
                        $localConn->table($module)->insert($mRecArr);
                    }
                }

                $syncedCount++;
            }

            if ($driver === 'sqlite') {
                $localConn->statement('PRAGMA foreign_keys = ON;');
            } else {
                $localConn->statement('SET FOREIGN_KEY_CHECKS=1;');
            }
            Cache::forget('audit_diagnostics_results');
            $localConn->commit();

            return [
                'status' => 'success',
                'count' => $syncedCount,
                'message' => "Berhasil mengintegrasikan {$syncedCount} record terverifikasi untuk modul '{$module}'.",
            ];
        } catch (\Throwable $e) {
            $localConn->rollBack();
            if ($driver === 'sqlite') {
                $localConn->statement('PRAGMA foreign_keys = ON;');
            } else {
                $localConn->statement('SET FOREIGN_KEY_CHECKS=1;');
            }
            Log::error("[MasterIngestionBridge] Merge error: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Gagal menggabungkan data: ' . $e->getMessage()];
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Bill;
use App\Models\BillType;
use Illuminate\Http\Request;

class BillController extends BaseWaliApiController
{
    public function index()
    {
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['unpaid' => [], 'paid' => []]);

        // Dispatch sync ke background queue agar API response tidak terblokir.
        // Cache TTL 30 menit: jika sync sudah berjalan, tidak perlu dispatch lagi.
        // Force sync bisa di-trigger dengan menghapus cache atau melalui admin panel.
        $syncCacheKey = "student_bills_synced_{$student->id}";
        if (!\Illuminate\Support\Facades\Cache::has($syncCacheKey)) {
            \Illuminate\Support\Facades\Cache::put($syncCacheKey, true, now()->addMinutes(30));
            dispatch(new \App\Jobs\SyncStudentBillsJob($student->id));
        }


        // Load student's school for UPT filtering
        $student->load('classroom.school');

        $allBills = Bill::with(['billType.billItem', 'billType.academicYear', 'academicYear'])
            ->whereHas('billType', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->where('student_id', $student->id)
            ->get();

        $studentSchoolName = $student->classroom?->school?->name ?? '';
        $entryYear = $student->getEntryYear() ?? date('Y');

        $filteredBills = $allBills->filter(function ($b) use ($studentSchoolName, $entryYear) {
            $btName = $b->billType?->name ?? '';
            if (!\App\Services\TransactionService::isBillTypeMatchingStudentSchoolUnit($btName, $studentSchoolName)) {
                return false;
            }
            
            $ay = $b->billType?->academicYear ?? $b->academicYear;
            if ($ay) {
                $startYear = $ay->getStartYearSafe();
                if ($startYear !== null && $startYear < $entryYear) {
                    return false;
                }
            }
            return true;
        });

        // Pre-load all paid/successful transactions for this student to prevent N+1 queries in the map loop.
        $studentTransactions = \App\Models\Transaction::with(['paymentMethod', 'admin', 'user', 'transactionDetails.bill'])
            ->where('student_id', $student->id)
            ->where('type', \App\Models\Transaction::TYPE_BILL)
            ->whereIn('status', [\App\Models\Transaction::STATUS_PAID, 'approved', 'SUCCESS', 'paid', 'PAID', 'success', 'SUCCESS', 'approved', 'APPROVED'])
            ->latest()
            ->get();

        $groupedBills = $filteredBills
            ->groupBy(function ($b) {
                $name = strtoupper(trim($b->billType?->name ?? 'TAGIHAN'));
                $ayId = $b->academic_year_id ?? $b->billType?->academic_year_id ?? 'default';
                return "{$name}_{$ayId}";
            })
            ->map(function ($items) use ($student, $studentTransactions) {
                $first = $items->first();
                
                // Get paid/successful transactions for this bill type group from memory
                $billIds = $items->pluck('id')->toArray();
                $payments = $studentTransactions->filter(function ($tx) use ($billIds) {
                    return $tx->transactionDetails->contains(function ($detail) use ($billIds) {
                        return $detail->deleted_at === null && in_array($detail->bill_id, $billIds);
                    });
                })->map(function ($tx) use ($billIds) {
                    $amount = 0;
                    foreach ($tx->transactionDetails as $detail) {
                        if ($detail->deleted_at !== null) {
                            continue;
                        }
                        if (in_array($detail->bill_id, $billIds)) {
                            $amount += $detail->amount ?? ($detail->bill->amount ?? 0);
                        }
                    }
                    
                    return [
                        'id' => $tx->id,
                        'amount' => $amount,
                        'date' => $tx->paid_at ?? $tx->created_at,
                        'method' => $tx->paymentMethod->name ?? 'Metode Lain',
                        'cashier' => $tx->admin->name ?? ($tx->user->name ?? 'Sistem'),
                    ];
                })
                ->filter(fn($p) => $p['amount'] > 0)
                ->values();

                $academicYearName = $first->academicYear?->name 
                    ?? $first->billType?->academicYear?->name 
                    ?? '-';

                $btNameUpper = strtoupper($first->billType?->name ?? '');
                $isZarkasi = str_contains($btNameUpper, 'ZARKASI');
                $isAplikasi = str_contains($btNameUpper, 'APLIKASI');
                $isSyahriah = str_contains($btNameUpper, 'SYAHR');

                if ($first->billType?->type === 'MONTHLY') {
                    $totalBill = 0;
                    $startYear = $first->academicYear?->start_year ?? date('Y');
                    $endYear = $first->academicYear?->end_year ?? ($startYear + 1);
                    foreach (array_merge(range(7, 12), range(1, 6)) as $m) {
                        $y = ($m >= 7) ? $startYear : $endYear;
                        $bDet = $items->firstWhere('month', (int)$m) ?? $items->firstWhere('month', (string)$m);
                        if ($bDet && $bDet->amount > 0) {
                            $totalBill += $bDet->amount;
                        } else {
                            $totalBill += \App\Services\TransactionService::resolveStudentRateForBillType($student, $first->billType, $m, $y);
                        }
                    }
                    $itemsCount = 12;
                } else {
                    $totalBill = $items->sum('amount');
                    $itemsCount = $items->count();
                }

                $totalPaid = $items->sum('paid_amount');
                $totalUnpaid = max(0, $totalBill - $totalPaid);

                return [
                    'bill_type_id' => $first->bill_type_id,
                    'bill_type_name' => $first->billType?->name ?? 'Tagihan',
                    'payment_input_type' => $first->billType?->payment_input_type ?? 'FIXED',
                    'academic_year' => $academicYearName,
                    'academic_year_id' => $first->academic_year_id ?? $first->billType?->academic_year_id,
                    'total' => $totalBill,
                    'paid' => $totalPaid,
                    'unpaid' => $totalUnpaid,
                    'items_count' => $itemsCount,
                    'unpaid_count' => $items->where('status', 'UNPAID')->count() + ($itemsCount - $items->count()),
                    'payments' => $payments->values(),
                ];
            });

        return response()->json([
            'unpaid' => $groupedBills->filter(fn($g) => $g['unpaid'] > 0)->values(),
            'paid' => $groupedBills->filter(fn($g) => $g['unpaid'] == 0)->values(),
        ]);
    }

    public function show($id)
    {
        $student = $this->resolveActiveStudent();
        if (!$student) return response()->json(['error' => 'Student not found'], 404);
        
        \App\Services\TransactionService::syncStudentBillsFromPaidTransactions($student->id);

        $billType = BillType::with(['billItem', 'academicYear'])->findOrFail($id);
        $academicYearId = request('academic_year_id');
        
        $query = Bill::with(['academicYear', 'transactionDetails' => function ($query) {
                $query->whereHas('transaction', function ($query) {
                    $query->where('status', \App\Models\Transaction::STATUS_PENDING_CONFIRMATION);
                });
            }])
            ->whereHas('billType', function ($q) {
                $q->whereNull('deleted_at');
            })
            ->where('student_id', $student->id)
            ->where('bill_type_id', $id);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $bills = $query->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($bill) {
                $bill->setAttribute('is_pending_confirmation', $bill->transactionDetails->isNotEmpty());
                return $bill;
            });

        $isZarkasi = str_contains(strtoupper($billType->name ?? ''), 'ZARKASI');
        $isAplikasi = str_contains(strtoupper($billType->name ?? ''), 'APLIKASI');
        $isSyahriah = str_contains(strtoupper($billType->name ?? ''), 'SYAHR');
        $isMonthly = $isZarkasi || $isAplikasi || $isSyahriah || ($billType->type === 'MONTHLY');

        if ($isMonthly) {
            $existingBills = $bills;
            $sampleBill = $existingBills->first();
            $academicYear = $sampleBill?->academicYear ?? $billType->academicYear;
            $startYear = $academicYear?->start_year ?? date('Y');
            
            $monthSequence = [7, 8, 9, 10, 11, 12, 1, 2, 3, 4, 5, 6];
            $fullBills = collect();

            foreach ($monthSequence as $m) {
                $year = ($m >= 7) ? (int)$startYear : (int)$startYear + 1;
                $found = $existingBills->first(function ($b) use ($m, $year) {
                    return (int)$b->month === (int)$m && (int)$b->year === (int)$year;
                });

                if (!$found) {
                    // Fallback amount determination dinamis dari PaymentRate DB
                    $amt = \App\Services\TransactionService::resolveStudentRateForBillType($student, $billType, $m, $year);

                    $found = new Bill([
                        'id' => "generated_{$billType->id}_{$student->id}_{$m}_{$year}",
                        'bill_type_id' => $billType->id,
                        'student_id' => $student->id,
                        'academic_year_id' => $academicYear?->id,
                        'month' => $m,
                        'year' => $year,
                        'amount' => $amt,
                        'paid_amount' => 0,
                        'remaining_amount' => $amt,
                        'status' => 'UNPAID',
                    ]);
                    $found->setAttribute('is_pending_confirmation', false);
                } else {
                    if ($found->amount <= 0) {
                        $expectedAmt = \App\Services\TransactionService::resolveStudentRateForBillType($student, $billType, $found->month, $found->year);
                        if ($expectedAmt > 0) {
                            $found->amount = $expectedAmt;
                            $found->remaining_amount = max(0, $expectedAmt - $found->paid_amount);
                        }
                    }
                }
                $fullBills->push($found);
            }
            $bills = $fullBills;
        }

        // Get academic year name from the first bill or billType fallback
        $academicYearName = $bills->first()?->academicYear?->name 
            ?? $billType->academicYear?->name 
            ?? null;

        $totalBill = $bills->sum('amount');
        $totalPaid = $bills->sum('paid_amount');
        $totalUnpaid = max(0, $totalBill - $totalPaid);

        return response()->json([
            'billType' => $billType,
            'academic_year_name' => $academicYearName,
            'bills' => $bills,
            'summary' => [
                'total' => $totalBill,
                'paid' => $totalPaid,
                'unpaid' => $totalUnpaid,
            ]
        ]);
    }
}

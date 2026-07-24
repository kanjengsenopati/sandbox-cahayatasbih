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

        // Load student's school for UPT filtering
        $student->load('classroom.school');

        $allBills = Bill::with(['billType.billItem', 'billType.academicYear', 'academicYear'])
            ->where('student_id', $student->id)
            ->get();

        // Strict UPT Filter: Only show bills matching student's UPT school
        $studentSchoolName = strtoupper($student->classroom?->school?->name ?? '');
        $isSmp = str_contains($studentSchoolName, 'SMP');
        $isMa = str_contains($studentSchoolName, 'MA') || str_contains($studentSchoolName, 'ALIYAH');
        $isPondok = str_contains($studentSchoolName, 'PONDOK') || str_contains($studentSchoolName, 'PPTQ');

        $filteredBills = $allBills->filter(function ($b) use ($isSmp, $isMa, $isPondok) {
            $btName = strtoupper($b->billType?->name ?? '');
            if ($isSmp) {
                return !str_contains($btName, 'PONDOK') && !str_contains($btName, 'MA');
            }
            if ($isMa) {
                return !str_contains($btName, 'PONDOK') && !str_contains($btName, 'SMP');
            }
            if ($isPondok) {
                return str_contains($btName, 'PONDOK');
            }
            return true;
        });

        $groupedBills = $filteredBills
            ->groupBy(function ($b) {
                $name = strtoupper(trim($b->billType?->name ?? 'TAGIHAN'));
                $ayId = $b->academic_year_id ?? $b->billType?->academic_year_id ?? 'default';
                return "{$name}_{$ayId}";
            })
            ->map(function ($items) use ($student) {
                $first = $items->first();
                
                // Get paid/successful transactions for this bill type group
                $billIds = $items->pluck('id')->toArray();
                $payments = \App\Models\Transaction::with(['paymentMethod', 'admin', 'user', 'transactionDetails.bill'])
                    ->where('student_id', $student->id)
                    ->where('type', \App\Models\Transaction::TYPE_BILL)
                    ->whereIn('status', [\App\Models\Transaction::STATUS_PAID, 'approved', 'SUCCESS'])
                    ->whereHas('transactionDetails', function ($query) use ($billIds) {
                        $query->whereIn('bill_id', $billIds)->whereNull('deleted_at');
                    })
                    ->latest()
                    ->get()
                    ->map(function ($tx) use ($billIds) {
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
                    ->filter(fn($p) => $p['amount'] > 0);

                $academicYearName = $first->academicYear?->name 
                    ?? $first->billType?->academicYear?->name 
                    ?? '-';

                return [
                    'bill_type_id' => $first->bill_type_id,
                    'bill_type_name' => $first->billType?->name ?? 'Tagihan',
                    'payment_input_type' => $first->billType?->payment_input_type ?? 'FIXED',
                    'academic_year' => $academicYearName,
                    'academic_year_id' => $first->academic_year_id ?? $first->billType?->academic_year_id,
                    'total' => $items->sum('amount'),
                    'paid' => $items->sum('paid_amount'),
                    'unpaid' => $items->sum(function($b) { return max(0, $b->amount - $b->paid_amount); }),
                    'items_count' => $items->count(),
                    'unpaid_count' => $items->where('status', 'UNPAID')->count(),
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
        
        $billType = BillType::with(['billItem', 'academicYear'])->findOrFail($id);
        $academicYearId = request('academic_year_id');
        
        $query = Bill::with(['academicYear', 'transactionDetails' => function ($query) {
                $query->whereHas('transaction', function ($query) {
                    $query->where('status', \App\Models\Transaction::STATUS_PENDING_CONFIRMATION);
                });
            }])
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

        // Get academic year name from the first bill or billType fallback
        $academicYearName = $bills->first()?->academicYear?->name 
            ?? $billType->academicYear?->name 
            ?? null;

        return response()->json([
            'billType' => $billType,
            'academic_year_name' => $academicYearName,
            'bills' => $bills,
            'summary' => [
                'total' => $bills->sum('amount'),
                'paid' => $bills->sum('paid_amount'),
                'unpaid' => $bills->sum('remaining_amount'),
            ]
        ]);
    }
}

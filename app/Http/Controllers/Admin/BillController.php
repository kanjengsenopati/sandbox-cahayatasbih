<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bill;
use App\Models\User;
use App\Models\School;
use App\Models\Contact;
use App\Models\Student;
use App\Models\BillType;
use App\Models\Classroom;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\ImportLog;
use App\Models\SaldoHistory;

use Exception;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\SavingHistory;
use App\Models\PpdbRegistration;
use App\Models\TransactionProof;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\SendNotifWaService;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Jobs\SendToPushNotificationJob;
use App\Jobs\SendToWhatsappNotificationJob;
use App\Http\Requests\Admin\BillPaymentRequest;
use App\Http\Requests\Admin\UpdateTransactionStatusRequest;
use App\Jobs\SyncStudentBillsJob;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        @set_time_limit(300);
        if (!Auth::user()->can('Manage Tagihan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $schools = School::orderBy('name')->hasSchool()->get();
        // Dapatkan Tahun Ajaran yang memiliki Jenis Bayar aktif (is_visible = true) atau sedang Aktif
        $academicYears = \App\Models\AcademicYear::where(function($query) {
            $query->where('is_active', true)
                  ->orWhereHas('billTypes', function ($q) {
                      $q->where('is_visible', true);
                  });
        })->orderBy('start_year', 'desc')->get();

        if ($studentId = request()->student_id) {
            // Dispatch sync ke background queue agar request HTTP tidak terblokir.
            // Cache TTL 30 menit: jika sync sudah berjalan dalam 30 menit terakhir,
            // tidak perlu dispatch lagi (data masih cukup fresh).
            $syncCacheKey = "student_bills_synced_{$studentId}";
            if (!Cache::has($syncCacheKey)) {
                Cache::put($syncCacheKey, true, now()->addMinutes(30));
                dispatch(new SyncStudentBillsJob($studentId, request()->academic_year_id));
            }

            $student = Student::with(['user', 'classroom.school', 'classroomHistories.classroom'])->find($studentId);
            if (!$student) {
                return redirect()->to(route('bill.index'))->with('error', 'Data siswa tidak ditemukan atau telah dihapus.');
            }

            $academicYearReq = request()->academic_year_id;
            
            if ($academicYearReq === 'all') {
                $academicYearId = null;
            } elseif (!empty($academicYearReq)) {
                $academicYearId = $academicYearReq;
            } else {
                // Default to active academic year if not specified
                $activeYear = \App\Models\AcademicYear::where('is_active', true)->first();
                $academicYearId = $activeYear ? $activeYear->id : null;
                if ($academicYearId) {
                    request()->merge(['academic_year_id' => $academicYearId]);
                }
            }

            // Reset academicYearId if it starts before student's entry year
            if ($academicYearId && $academicYearId !== 'all') {
                $selectedYear = \App\Models\AcademicYear::find($academicYearId);
                $selectedStartYear = $selectedYear?->getStartYearSafe();
                if ($selectedStartYear !== null && $student->getEntryYear() > $selectedStartYear) {
                    $academicYearId = null;
                    request()->query->set('academic_year_id', 'all');
                    request()->request->set('academic_year_id', 'all');
                }
            }

            $preloadedRates = TransactionService::getCachedPreloadedRates();
            $allStudentBills = Bill::where('student_id', $studentId)
                ->whereNull('deleted_at')
                ->with('billType')
                ->get();

            $billMonth = $this->getBills($studentId, BillType::TYPE_MONTHLY, $academicYearId);
            $billOthers = $this->getBills($studentId, BillType::TYPE_OTHER, $academicYearId);

            // Detect OTHER-type BillTypes yang punya tarif aktif untuk siswa ini tapi belum ada tagihan.
            // Data ini dipakai untuk shortcut Generate di halaman Data Pembayaran (tab Lainnya).
            $ungeneratedOtherRates = $this->getUngeneratedRatesForStudent($student, BillType::TYPE_OTHER, $billOthers->pluck('id')->toArray(), $academicYearId, $preloadedRates);
            
            // Lakukan hal yang sama untuk MONTHLY-type BillTypes (tab Bulanan/Kilat)
            $ungeneratedMonthlyRates = $this->getUngeneratedRatesForStudent($student, BillType::TYPE_MONTHLY, $billMonth->pluck('id')->toArray(), $academicYearId, $preloadedRates);

            return view('admins.bill.index', compact('student', 'billMonth', 'billOthers', 'schools', 'academicYears', 'preloadedRates', 'allStudentBills', 'ungeneratedOtherRates', 'ungeneratedMonthlyRates'));
        }

        if (request()->ajax()) {
            if (request()->tab === 'archive') {
                return $this->getArchiveTransactionData();
            }
            return $this->getTransactionData();
        }

        return view('admins.bill.index', compact('schools', 'academicYears'));
    }

    private function getBills($studentId, $type, $academicYearId = null)
    {

        $student = Student::with('classroom.school')->find($studentId);
        $studentSchoolName = $student?->classroom?->school?->name ?? '';
        $entryYear = $student?->getEntryYear() ?? date('Y');

        $studentBillTypeIds = Bill::with(['billType.billItem'])
            ->where('student_id', $studentId)
            ->whereNull('deleted_at')
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->get()
            ->filter(function ($b) use ($studentSchoolName) {
                $bTypeName = $b->billType->name ?? '';
                $bItemName = $b->billType->billItem->name ?? '';
                return TransactionService::isBillTypeMatchingStudentSchoolUnit($bTypeName, $studentSchoolName)
                    || TransactionService::isBillTypeMatchingStudentSchoolUnit($bItemName, $studentSchoolName);
            })
            ->pluck('bill_type_id')
            ->unique();

        $query = BillType::with(['billItem', 'academicYear', 'bills' => function ($query) use ($studentId, $academicYearId) {
                $query->where('student_id', $studentId);
                if ($academicYearId) {
                    $query->where('academic_year_id', $academicYearId);
                }
                $query->with(['classroom.school']);
            }])
            ->where('type', $type)
            ->whereIn('id', $studentBillTypeIds);

        $preloadedRates = \App\Services\TransactionService::getCachedPreloadedRates();

        $allStudentBills = Bill::where('student_id', $studentId)
            ->whereNull('deleted_at')
            ->with('billType')
            ->get();

        return $query->latest()
            ->get()
            ->filter(function($item) use ($student, $studentSchoolName, $entryYear, $academicYearId, $preloadedRates) {
                if ($item->is_visible === false) {
                    return false;
                }

                // ATURAN KHUSUS SISWA KELAS 12 MA:
                // Hide tagihan Tahun Ajaran < 2026/2027 (bila tidak difilter TA spesifik)
                // Jenis tagihan yang muncul ditentukan oleh mapping tarif aktif (SST) di guard bawah,
                // bukan whitelist hardcoded, agar semua tagihan yang sudah di-generate admin pasti tampil.
                if (TransactionService::isClass12MA($student)) {
                    if (!$academicYearId && TransactionService::isBillBeforeAcademicYear2026($item->academicYear)) {
                        return false;
                    }
                }

                if (!TransactionService::isBillTypeMatchingStudentSchoolUnit($item->name, $studentSchoolName)) {
                    return false;
                }
                if (!$academicYearId && $item->academicYear) {
                    $startYear = $item->academicYear->getStartYearSafe();
                    if ($startYear !== null && $startYear < $entryYear) {
                        return false;
                    }
                }

                // SINGLE SOURCE OF TRUTH (SST) ENFORCEMENT:
                // Tagihan HANYA boleh muncul di profil siswa jika Admin SUDAH me-mapping kelas/siswa pada Payment Rate untuk BillType ini,
                // ATAU jika siswa memiliki tagihan berstatus PAID / paid_amount > 0 yang harus dipertahankan.
                $hasPaidBills = $item->bills->contains(fn($b) => $b->status === Bill::STATUS_PAID || (int)$b->paid_amount > 0);
                if (!$hasPaidBills && !TransactionService::hasActiveRateForStudent($student, $item, $preloadedRates)) {
                    return false;
                }

                return true;
            })
            ->map(fn($item) => $this->calculateBillTotals($item, $student, $preloadedRates, $allStudentBills))
            ->values();
    }

    private function calculateBillTotals($item, $student, $preloadedRates = null, $allStudentBills = null)
    {
        $bills = $item->bills;
        $upperName = strtoupper($item->name ?? '');

        $isZarkasi = str_contains($upperName, 'ZARKASI');
        $isAplikasi = str_contains($upperName, 'APLIKASI');
        $isSyahriah = str_contains($upperName, 'SYAHR');

        if ($item->type === 'MONTHLY') {
            $totalBill = 0;
            $startYear = $item->academicYear?->start_year ?? date('Y');
            $endYear = $item->academicYear?->end_year ?? ($startYear + 1);
            foreach (array_merge(range(7, 12), range(1, 6)) as $m) {
                $y = ($m >= 7) ? $startYear : $endYear;
                $bDet = $bills->firstWhere('month', (int)$m) ?? $bills->firstWhere('month', (string)$m);
                if ($bDet !== null) {
                    $totalBill += $bDet->amount;
                } else {
                    $totalBill += TransactionService::resolveStudentRateForBillType($student, $item, $m, $y, $preloadedRates);
                }
            }
            $item->total_bill = $totalBill;
        } else {
            $item->total_bill = $bills->sum('amount');
        }

        $item->total_paid = $bills->sum('paid_amount');

        $item->total_unpaid = max(0, $item->total_bill - $item->total_paid);

        return $item;
    }

    /**
     * Deteksi BillType yang sudah punya PaymentRate aktif di-mapping ke kelas/siswa ini,
     * tapi belum ada tagihan yang di-generate. Digunakan untuk shortcut Generate di UI.
     */
    private function getUngeneratedRatesForStudent($student, $type, $excludeBillTypeIds = [], $academicYearId = null, $preloadedRates = null)
    {
        $result = collect();
        if (!$student || !$student->classroom_id) return $result;

        $studentSchoolName = $student->classroom?->school?->name ?? '';
        $entryYear = $student->getEntryYear() ?? date('Y');
        if ($preloadedRates === null) {
            $preloadedRates = TransactionService::getCachedPreloadedRates();
        }

        // Cari BillType yang sesuai tipe, belum ada tagihannya, dan visible
        $candidateBillTypes = BillType::with(['billItem', 'academicYear'])
            ->where('type', $type)
            ->whereNull('deleted_at')
            ->where(fn($q) => $q->whereNull('is_visible')->orWhere('is_visible', true))
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->whereNotIn('id', $excludeBillTypeIds)
            ->get();

        foreach ($candidateBillTypes as $bt) {
            // Guard: UPT isolation
            if (!TransactionService::isBillTypeMatchingStudentSchoolUnit($bt->name, $studentSchoolName)) continue;

            // Guard: Entry year
            if ($bt->academicYear) {
                $startYear = $bt->academicYear->getStartYearSafe();
                if ($startYear !== null && $startYear < $entryYear) continue;
            }

            // Guard: Class 12 MA — hide TA < 2026/2027
            if (TransactionService::isClass12MA($student)) {
                if (TransactionService::isBillBeforeAcademicYear2026($bt->academicYear)) continue;
            }

            // Check apakah ada active rate untuk siswa ini
            if (!TransactionService::hasActiveRateForStudent($student, $bt, $preloadedRates)) continue;

            // Cari rate yang matching
            $ratesForBt = $preloadedRates->where('bill_type_id', $bt->id);
            $matchingRate = $ratesForBt->first(function ($r) use ($student) {
                if ($r->type === \App\Models\PaymentRate::TYPE_TRANSFER) {
                    return $r->paymentRateStudents->whereNull('deleted_at')->contains('student_id', $student->id);
                }
                if ($r->type === \App\Models\PaymentRate::TYPE_REGULAR) {
                    $classMatch = $r->paymentRateClassrooms->whereNull('deleted_at')->contains('classroom_id', $student->classroom_id);
                    if (!$classMatch) return false;
                    if (!empty($r->gender)) {
                        $genders = array_map('trim', explode(',', $r->gender));
                        if (!in_array($student->gender, $genders)) return false;
                    }
                    if (!empty($r->jamaah_status)) {
                        $statuses = array_map('trim', explode(',', $r->jamaah_status));
                        $studentStatus = $student->user?->jamaah_status ?? 'NON_JAMAAH';
                        if (!in_array($studentStatus, $statuses)) return false;
                    }
                    return true;
                }
                return false;
            });

            if ($matchingRate) {
                $result->push((object) [
                    'rate_id' => $matchingRate->id,
                    'bill_type_id' => $bt->id,
                    'bill_type_name' => $bt->name,
                    'academic_year_name' => $bt->academicYear?->name ?? '-',
                    'amount' => $matchingRate->amount ?? 0,
                ]);
            }
        }

        return $result;
    }

    private function getTransactionData()
    {
        @set_time_limit(300);
        $transferMethodIds = PaymentMethod::where('type', PaymentMethod::TYPE_TRANSFER)->pluck('id')->toArray();

        $transactions = Transaction::with([
            'student',
            'paymentMethod',
            'activeProof.bank',
            'transactionProofs.bank',
            'transactionDetails.bill.billType.academicYear',
            'transactionDetails.bill.academicYear',
            'transactionDetails.saldoHistory',
            'transactionDetails.savingHistory',
            'transactionDetails.ppdbRegistration'
        ])
            ->whereIn('payment_method_id', $transferMethodIds)
            ->where('type', Transaction::TYPE_BILL)
            ->where('status', Transaction::STATUS_PENDING_CONFIRMATION)
            ->hasSchool()
            ->latest();

        return DataTables::of($transactions)
            ->addColumn('proof', fn($transaction) => $this->formatProofColumn($transaction))
            ->editColumn('pay_amount', fn($transaction) => $this->formatPayAmountWithDetailsColumn($transaction))
            ->editColumn('status', fn($transaction) => $this->formatStatusColumn($transaction))
            ->addColumn('action', fn($transaction) => $this->formatActionColumn($transaction))
            ->addColumn('bank_recipient', function ($transaction) {
                $proof = $transaction->activeProof ?? $transaction->transactionProofs->first();
                $bank = $proof?->bank;
                if (!$bank) return '-';
                return "{$bank->name}<br><small class='text-muted'>No. Rek: {$bank->account_number}</small><br><small class='text-muted'>A.N: {$bank->account_name}</small>";
            })
            ->rawColumns(['proof', 'action', 'status', 'bank_recipient', 'pay_amount'])
            ->make(true);
    }

    private function formatProofColumn($transaction)
    {
        $proof = $transaction->activeProof ?? $transaction->transactionProofs->first();
        $proofUrl = $proof?->proof_image_url ?? $proof?->proof_image;
        if (!$proofUrl) return '<span class="text-muted fs-8 fst-italic">-</span>';
        $fallbackSvg = "data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' viewBox=\\'0 0 100 100\\'%3E%3Crect width=\\'100\\' height=\\'100\\' fill=\\'%23f1f5f9\\'/%3E%3Ctext x=\\'50%25\\' y=\\'50%25\\' dominant-baseline=\\'middle\\' text-anchor=\\'middle\\' font-family=\\'sans-serif\\' font-size=\\'11\\' fill=\\'%2394a3b8\\'%3ETidak Ada%3C/text%3E%3C/svg%3E";
        return "<img src='{$proofUrl}' class='img-fluid img-thumbnail cursor-pointer view-proof-image shadow-sm' data-src='{$proofUrl}' onerror=\"this.onerror=null; this.src='{$fallbackSvg}';\" style='max-width: 80px; height: auto; border-radius: 8px;' alt='Bukti Transfer' title='Klik untuk melihat bukti'>";
    }

    private function formatStatusColumn($transaction)
    {
        $statusLabels = [
            Transaction::STATUS_PENDING => 'Belum Dibayar',
            Transaction::STATUS_PENDING_PAYMENT => 'Menunggu Pembayaran',
            Transaction::STATUS_PENDING_CONFIRMATION => 'Menunggu Verifikasi',
            Transaction::STATUS_PAID => 'Lunas',
            Transaction::STATUS_EXPIRED => 'Kedaluwarsa',
            Transaction::STATUS_CANCELLED => 'Dibatalkan',
            Transaction::STATUS_REJECTED => 'Ditolak'
        ];

        $statusClasses = [
            Transaction::STATUS_PENDING => 'badge badge-primary',
            Transaction::STATUS_PENDING_PAYMENT => 'badge badge-warning',
            Transaction::STATUS_PENDING_CONFIRMATION => 'badge badge-danger',
            Transaction::STATUS_PAID => 'badge badge-success',
            Transaction::STATUS_EXPIRED => 'badge badge-secondary',
            Transaction::STATUS_CANCELLED => 'badge badge-secondary',
            Transaction::STATUS_REJECTED => 'badge badge-danger'
        ];

        $status = $transaction->status;
        $statusLabel = $statusLabels[$status] ?? 'Unknown';
        $statusClass = $statusClasses[$status] ?? 'badge badge-secondary';

        $statusHtml = "<span class='{$statusClass}'>{$statusLabel}</span>";
        if ($transaction->created_at) {
            $formattedDate = strtoupper(\Carbon\Carbon::parse($transaction->created_at)->translatedFormat('d-M-Y , H : i'));
            $statusHtml .= "<br><small class='text-muted fst-italic'>{$formattedDate}</small>";
        }
        if ($status === Transaction::STATUS_REJECTED && $transaction->activeProof?->note) {
            $statusHtml .= "<br><small class='text-danger'>{$transaction->activeProof->note}</small>";
        }

        return $statusHtml;
    }

    private function formatActionColumn($transaction)
    {
        if (!Auth::user()->can('Edit Tagihan')) {
            return '';
        }

        $id = $transaction->id;
        $status = $transaction->status;

        $action = '<div class="d-flex flex-column">';
        $action .= '<select class="form-select form-select-sm" id="status-' . $id . '" onchange="updateStatus(this.value, \'' . $id . '\')">';
        $action .= '<option value="">Pilih Status</option>';
        $action .= '<option value="' . Transaction::STATUS_PAID . '"' . ($status == Transaction::STATUS_PAID ? ' selected' : '') . '>Lunas</option>';
        $action .= '<option value="' . Transaction::STATUS_REJECTED . '"' . ($status == Transaction::STATUS_REJECTED ? ' selected' : '') . '>Ditolak</option>';
        $action .= '</select>';
        $action .= '<input type="hidden" name="note" id="note-' . $id . '">';
        $action .= '<button class="btn btn-sm btn-primary mt-2" onclick="saveStatus(\'' . $id . '\')">Simpan</button>';
        $action .= '</div>';

        return $action;
    }

    private function getArchiveTransactionData()
    {
        @set_time_limit(300);
        $transferMethodIds = PaymentMethod::where('type', PaymentMethod::TYPE_TRANSFER)->pluck('id')->toArray();

        $transactions = Transaction::with([
            'student',
            'paymentMethod',
            'activeProof.bank',
            'transactionProofs.bank',
            'admin',
            'transactionDetails.bill.billType.academicYear',
            'transactionDetails.bill.academicYear',
            'transactionDetails.saldoHistory',
            'transactionDetails.savingHistory',
            'transactionDetails.ppdbRegistration'
        ])
            ->whereIn('payment_method_id', $transferMethodIds)
            ->where('type', Transaction::TYPE_BILL)
            ->where('status', Transaction::STATUS_PAID)
            ->where('is_deleted_from_archive', false)
            ->hasSchool();

        if ($searchStudent = request()->search_student) {
            $transactions->whereHas('student', function ($q) use ($searchStudent) {
                $q->where('name', 'like', "%{$searchStudent}%")
                  ->orWhere('nis', 'like', "%{$searchStudent}%");
            });
        }
        if ($startDate = request()->start_date) {
            $transactions->whereDate('updated_at', '>=', $startDate);
        }
        if ($endDate = request()->end_date) {
            $transactions->whereDate('updated_at', '<=', $endDate);
        }

        $transactions->latest('updated_at');

        return DataTables::of($transactions)
            ->addColumn('proof', fn($transaction) => $this->formatProofColumn($transaction))
            ->editColumn('pay_amount', fn($transaction) => $this->formatPayAmountWithDetailsColumn($transaction))
            ->editColumn('status', fn($transaction) => $this->formatStatusColumn($transaction))
            ->addColumn('action', fn($transaction) => $this->formatArchiveActionColumn($transaction))
            ->addColumn('bank_recipient', function ($transaction) {
                $proof = $transaction->activeProof ?? $transaction->transactionProofs->first();
                $bank = $proof?->bank;
                if (!$bank) return '-';
                return "{$bank->name}<br><small class='text-muted'>No. Rek: {$bank->account_number}</small><br><small class='text-muted'>A.N: {$bank->account_name}</small>";
            })
            ->addColumn('officer', function ($transaction) {
                return $transaction->admin?->name ?? '-';
            })
            ->addColumn('updated_at_formatted', function ($transaction) {
                return $transaction->updated_at ? \Carbon\Carbon::parse($transaction->updated_at)->translatedFormat('d F Y H:i') : '-';
            })
            ->rawColumns(['proof', 'action', 'status', 'bank_recipient', 'pay_amount'])
            ->make(true);
    }

    private function formatPayAmountWithDetailsColumn($transaction)
    {
        $formattedTotal = 'Rp ' . number_format($transaction->pay_amount, 0, ',', '.');
        
        // Group details by BillType + AcademicYear
        $grouped = [];
        $totalItemCount = 0;

        foreach ($transaction->transactionDetails as $detail) {
            $bill = $detail->bill;
            if ($bill) {
                $billType = $bill->billType;
                $billTypeName = $billType?->name ?? 'Tagihan Siswa';
                
                $academicYear = $billType?->academicYear ?? $bill->academicYear;
                $academicYearName = $academicYear?->name ?? '';

                $monthName = $bill->translated_month ?? $bill->getTranslatedMonthAttribute();
                $periodLabel = $monthName ? $monthName : ($bill->year ? "Tahun {$bill->year}" : 'Sekali Bayar');
                
                $groupKey = $billTypeName . '___' . $academicYearName;

                if (!isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [
                        'bill_type_name' => $billTypeName,
                        'academic_year' => $academicYearName,
                        'months' => [],
                        'total_amount' => 0,
                    ];
                }
                
                $itemAmount = $detail->amount > 0 ? $detail->amount : ($bill->amount ?? 0);
                $grouped[$groupKey]['months'][] = $periodLabel;
                $grouped[$groupKey]['total_amount'] += $itemAmount;
                $totalItemCount++;
            } elseif ($detail->saldoHistory || $transaction->type === Transaction::TYPE_SALDO) {
                $groupKey = 'TOPUP_SALDO';
                if (!isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [
                        'bill_type_name' => 'Top Up Saldo Uang Saku',
                        'academic_year' => '',
                        'months' => ['Uang Saku PWA'],
                        'total_amount' => max(0, $transaction->pay_amount - ($transaction->unique_payment ?? 0)),
                    ];
                    $totalItemCount++;
                }
            } elseif ($detail->savingHistory || $transaction->type === Transaction::TYPE_SAVING) {
                $groupKey = 'SAVING';
                if (!isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [
                        'bill_type_name' => 'Setoran Tabungan Santri',
                        'academic_year' => '',
                        'months' => ['Tabungan'],
                        'total_amount' => max(0, $transaction->pay_amount - ($transaction->unique_payment ?? 0)),
                    ];
                    $totalItemCount++;
                }
            } elseif ($detail->ppdbRegistration || $transaction->type === Transaction::TYPE_PPDB) {
                $groupKey = 'PPDB';
                if (!isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [
                        'bill_type_name' => 'Biaya Pendaftaran PPDB',
                        'academic_year' => '',
                        'months' => ['Pendaftaran'],
                        'total_amount' => max(0, $transaction->pay_amount - ($transaction->unique_payment ?? 0)),
                    ];
                    $totalItemCount++;
                }
            } elseif ($detail->amount > 0) {
                $groupKey = 'DETAIL_MANUAL_' . $detail->id;
                $grouped[$groupKey] = [
                    'bill_type_name' => 'Tagihan Siswa (' . ($transaction->payment_code ?? 'Manual') . ')',
                    'academic_year' => '',
                    'months' => ['Biaya Pendidikan'],
                    'total_amount' => $detail->amount,
                ];
                $totalItemCount++;
            }
        }

        // Fallback if transactionDetails has no mapped items or empty (ensures 100% of items have full detail):
        if (empty($grouped)) {
            $typeLabel = match($transaction->type) {
                Transaction::TYPE_SALDO => 'Top Up Saldo Uang Saku',
                Transaction::TYPE_SAVING => 'Setoran Tabungan Santri',
                Transaction::TYPE_PPDB => 'Biaya Pendaftaran PPDB',
                default => 'Pembayaran Tagihan Siswa'
            };
            $periodLabel = $transaction->payment_code ? "Kode: {$transaction->payment_code}" : 'Pembayaran Transfer';
            $grouped['FALLBACK'] = [
                'bill_type_name' => $typeLabel,
                'academic_year' => '',
                'months' => [$periodLabel],
                'total_amount' => max(0, $transaction->pay_amount - ($transaction->unique_payment ?? 0)),
            ];
            $totalItemCount = 1;
        }

        $buttonText = "{$totalItemCount} Item Tagihan";
        if ($transaction->type === Transaction::TYPE_SALDO) {
            $buttonText = "Detail Top Up";
        } elseif ($transaction->type === Transaction::TYPE_SAVING) {
            $buttonText = "Detail Tabungan";
        } elseif ($transaction->type === Transaction::TYPE_PPDB) {
            $buttonText = "Detail PPDB";
        }

        $collapseId = 'collapse-bills-' . $transaction->id;

        $html = "<div class='d-flex flex-column align-items-start gap-1'>";
        $html .= "  <span class='text-emerald-600 fw-boldest fs-6'>{$formattedTotal}</span>";
        $html .= "  <button type='button' class='btn btn-xs btn-light-primary py-1 px-2.5 rounded-[12px] fs-8 fw-bold d-inline-flex align-items-center gap-1 mt-1' data-bs-toggle='collapse' data-bs-target='#{$collapseId}' aria-expanded='false'>";
        $html .= "    <i class='fas fa-list-ul fs-9'></i> {$buttonText} <i class='fas fa-chevron-down ms-1 fs-9'></i>";
        $html .= "  </button>";
        $html .= "</div>";

        // Wide Expandable Grouped Table Panel matching Gambar 2 & 3
        $html .= "<div class='collapse mt-3 text-start' id='{$collapseId}'>";
        $html .= "  <div class='card card-body p-4 rounded-[16px] border border-gray-200 bg-white shadow-lg' style='min-width: 650px; width: 100%; max-width: 850px;'>";
        $html .= "    <div class='table-responsive'>";
        $html .= "      <table class='table align-middle table-row-dashed fs-8 gy-3 mb-0'>";
        $html .= "        <thead>";
        $html .= "          <tr class='text-start text-gray-500 fw-bolder fs-8 text-uppercase gs-0 border-bottom border-gray-300'>";
        $html .= "            <th style='width: 5%'>NO</th>";
        $html .= "            <th style='width: 35%'>NAMA TAGIHAN & TAHUN AJARAN</th>";
        $html .= "            <th style='width: 40%'>BULAN TERBAYAR</th>";
        $html .= "            <th class='text-end' style='width: 20%'>NOMINAL TOTAL</th>";
        $html .= "          </tr>";
        $html .= "        </thead>";
        $html .= "        <tbody class='text-gray-700 fw-bold'>";

        $rowNo = 1;
        $grandTotal = 0;

        foreach ($grouped as $groupData) {
            $typeName = $groupData['bill_type_name'];
            $taName = $groupData['academic_year'];
            $months = $groupData['months'];
            $groupTotalFormatted = 'Rp ' . number_format($groupData['total_amount'], 0, ',', '.');
            $grandTotal += $groupData['total_amount'];

            $html .= "          <tr>";
            $html .= "            <td class='align-top pt-3'>{$rowNo}</td>";
            $html .= "            <td class='align-top pt-3'>";
            $html .= "              <span class='d-block text-dark fw-bolder fs-7 text-uppercase mb-1'>{$typeName}</span>";
            if ($taName) {
                $html .= "              <span class='badge badge-light-info text-info border border-info border-opacity-40 rounded-pill px-2.5 py-1 fs-9 fw-bold d-inline-flex align-items-center gap-1'>";
                $html .= "                <i class='fas fa-calendar-alt fs-9 text-info'></i> TA {$taName}";
                $html .= "              </span>";
            }
            $html .= "            </td>";
            $html .= "            <td class='align-top pt-3'>";
            $html .= "              <div class='d-flex flex-wrap gap-1.5'>";
            foreach ($months as $m) {
                $html .= "                <span class='badge bg-white text-info border border-info border-opacity-60 rounded-pill px-3 py-1.5 fs-8 fw-semibold shadow-xs'>{$m}</span>";
            }
            $html .= "              </div>";
            $html .= "            </td>";
            $html .= "            <td class='text-end align-top pt-3 text-dark fw-bolder fs-7'>{$groupTotalFormatted}</td>";
            $html .= "          </tr>";

            $rowNo++;
        }

        $grandTotalFormatted = 'Rp ' . number_format($grandTotal, 0, ',', '.');

        $html .= "        </tbody>";
        $html .= "        <tfoot>";
        $html .= "          <tr class='border-top border-gray-300 fw-boldest fs-7'>";
        $html .= "            <td colspan='3' class='text-end pt-4 text-dark'>Total Pembayaran Keseluruhan:</td>";
        $html .= "            <td class='text-end pt-4 text-emerald-600 fs-6'>{$grandTotalFormatted}</td>";
        $html .= "          </tr>";
        $html .= "        </tfoot>";
        $html .= "      </table>";
        $html .= "    </div>";
        $html .= "  </div>";
        $html .= "</div>";

        return $html;
    }

    private function formatArchiveActionColumn($transaction)
    {
        if (!Auth::user()->can('Edit Tagihan')) {
            return '';
        }

        $daysDiff = $transaction->updated_at ? $transaction->updated_at->diffInDays(now()) : 0;
        $canDelete = $daysDiff > 30;

        if ($canDelete) {
            return "<button class='btn btn-danger btn-sm delete-archive-btn' data-id='{$transaction->id}'>
                        <i class='fas fa-trash me-1'></i> Hapus
                    </button>";
        } else {
            return "<button class='btn btn-danger btn-sm' disabled data-bs-toggle='tooltip' title='Hapus dinonaktifkan karena usia arsip kurang dari 30 hari'>
                        <i class='fas fa-trash me-1'></i> Hapus
                    </button>";
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BillPaymentRequest $request)
    {
        $studentId = $request->student_id;
        $lockKey = "student_transaction_{$studentId}";

        // COBA DAPATKAN KUNCI (ATOMIC)
        // Kunci selama 15 detik (bukan 300 detik) untuk mencegah deadlock kasir
        $lock = Cache::lock($lockKey, 15);

        if (!$lock->get()) {
            return redirect()->back()->with('error', 'Transaksi sedang diproses, mohon tunggu sebentar.');
        }

        try {
            $paymentMethodType = $request->payment_method;

            // Validasi Logika Bisnis Tambahan: 1 Query Batching (Mencegah N+1 Loop)
            $alreadyPaidCount = Bill::whereIn('id', $request->bill_ids)->where('status', Bill::STATUS_PAID)->count();
            if ($alreadyPaidCount > 0) {
                throw new Exception("Sebagian tagihan yang dipilih sudah berstatus lunas. Silakan muat ulang halaman.");
            }

            // createTransaction menggunakan DB::transaction internal — ACID terjaga
            $transaction = TransactionService::createTransaction($request, $paymentMethodType, Transaction::TYPE_BILL);

            if ($transaction->status == Transaction::STATUS_PAID && $transaction?->student?->user?->phone) {
                // Dispatch notifikasi di LUAR transaction agar tidak mempengaruhi atomicity
                TransactionService::dispatchNotifications($transaction);
            }

            // Invalidate sync cache agar job dipicu lagi saat santri buka tagihan
            Cache::forget("student_bills_synced_{$studentId}");

            return redirect()->back()->with('success', "Transaksi pembayaran berhasil");

        } catch (\Throwable $th) {
            Log::error($th);

            return redirect()->back()->with('error', "Transaksi pembayaran gagal: " . $th->getMessage());
        } finally {
            // Selalu lepas kunci di blok finally agar kasir tidak pernah terkunci
            optional($lock)->release();
        }
    }




    /**
     * Display the specified resource.
     */

    public function show(string $id)
    {
        if (!Auth::user()->can('Manage Tagihan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $transaction = Transaction::with('student', 'paymentMethod', 'activeProof')
            ->findOrFail($id);
        return view('admins.bill.show', compact('transaction'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransactionStatusRequest $request, string $id)
    {
        $transaction = Transaction::findOrFail($id);
        $data = $request->validated();
        $data['admin_id'] = Auth::id();
        if ($request->status == Transaction::STATUS_PAID) {
            $data['paid_at'] = now();
        }

        $result = TransactionService::updateStatusPaymentTransfer($data, $transaction);

        if ($result['status']) {
            return $this->postSuccessResponse($result['message'], $result['transaction']);
        } else {
            return $this->failedResponse($result['message']);
        }
    }

    public function destroy(string $id)
    {
        if (!Auth::user()->can('Edit Tagihan')) {
            return response()->json([
                'code' => '403',
                'message' => 'Anda tidak memiliki hak akses untuk menghapus arsip.'
            ], 403);
        }

        $transaction = Transaction::findOrFail($id);

        $daysDiff = $transaction->updated_at ? $transaction->updated_at->diffInDays(now()) : 0;
        if ($daysDiff <= 30) {
            return response()->json([
                'code' => '400',
                'message' => 'Gagal menghapus: Arsip hanya dapat dihapus jika usianya sudah lebih dari 30 hari.'
            ], 400);
        }

        $transaction->update([
            'is_deleted_from_archive' => true
        ]);

        return response()->json([
            'code' => '200',
            'message' => 'Arsip riwayat pembayaran berhasil dihapus.'
        ]);
    }

    public function getBillData()
    {

        $id = request()->student_id;
        $student = Student::find($id);
        if (!$student) {
            return redirect()->route('bill.index')->with('error', 'Data siswa tidak ditemukan.');
        }

        // Mengambil tagihan bulanan
        $billMonth = BillType::with('billItem', 'academicYear')
            ->where('type', BillType::TYPE_MONTHLY)
            ->whereHas('bills', function ($query) use ($id) {
                $query->where('student_id', $id);
            })
            ->latest()
            ->get()
            ->map(function ($item) use ($id) {
                $item->total_unpaid = Bill::where('student_id', $id)
                    ->where('bill_type_id', $item->id)
                    ->sum(\DB::raw('amount - paid_amount'));

                $item->total_paid = Bill::where('student_id', $id)
                    ->where('bill_type_id', $item->id)
                    ->sum('paid_amount');

                return $item;
            });

        // Mengambil tagihan lainnya
        $billOthers = BillType::where('type', BillType::TYPE_OTHER)
            ->whereHas('bills', function ($query) use ($id) {
                $query->where('student_id', $id);
            })
            ->latest()
            ->get()
            ->map(function ($item) use ($id) {
                $item->total_unpaid = Bill::where('student_id', $id)
                    ->where('bill_type_id', $item->id)
                    ->sum(\DB::raw('amount - paid_amount'));
                $item->total_paid = Bill::where('student_id', $id)
                    ->where('bill_type_id', $item->id)
                    ->sum('paid_amount');

                return $item;
            });


        return view('admins.bill.show', compact('student', 'billMonth', 'billOthers'));
    }

    public function summaryBill()
    {
        $requestData = request()->validate([
            'student_id'   => 'required|exists:students,id',
            'bill_type_id' => 'required|exists:bill_types,id',
        ]);

        $student = Student::with(['user', 'classroom.school'])->findOrFail($requestData['student_id']);
        $billType = BillType::findOrFail($requestData['bill_type_id']);

        $bills = Bill::with('transactions')
            ->where('student_id', $student->id)
            ->where('bill_type_id', $billType->id)
            ->orderBy('month')
            ->get();

        if ($billType->type === 'MONTHLY') {
            $totalBill = 0;
            $startYear = $billType->academicYear?->start_year ?? date('Y');
            $endYear = $billType->academicYear?->end_year ?? ($startYear + 1);
            $preloadedRates = TransactionService::getCachedPreloadedRates();

            foreach (array_merge(range(7, 12), range(1, 6)) as $m) {
                $y = ($m >= 7) ? $startYear : $endYear;
                $bDet = $bills->firstWhere('month', (int)$m) ?? $bills->firstWhere('month', (string)$m);
                if ($bDet && $bDet->amount > 0) {
                    $totalBill += $bDet->amount;
                } else {
                    $totalBill += TransactionService::resolveStudentRateForBillType($student->id, $billType, $m, $y, $preloadedRates);
                }
            }
        } else {
            $totalBill = $bills->sum('amount');
        }

        $totalPaid   = $bills->sum('paid_amount');
        $totalUnpaid = max(0, $totalBill - $totalPaid);

        $summary = [
            'total_bill'   => $totalBill,
            'total_paid'   => $totalPaid,
            'total_unpaid' => $totalUnpaid,
        ];

        $paymentMethods = PaymentMethod::latest()->get();

        if (request()->ajax()) { return view('admins.bill.summary-ajax', compact('student', 'billType', 'bills', 'summary', 'paymentMethods')); }
        return view('admins.bill.summary', compact(
            'student',
            'billType',
            'bills',
            'summary',
            'paymentMethods'
        ));
    }

    // public function summaryBill()
    // {
    //     $requestData = request()->only(['student_id', 'bill_type_id']);

    //     $student = Student::findOrFail($requestData['student_id']);
    //     $billType = BillType::findOrFail($requestData['bill_type_id']);

    //     $billType->load(['bills' => function ($query) use ($student, $billType) {
    //         $query->where('student_id', $student->id)->where('bill_type_id', $billType->id);
    //     }]);

    //     $totalBill = $billType->bills->sum('amount');
    //     $totalPaid = $billType->bills->where('status', Bill::STATUS_PAID)->sum('amount');
    //     $totalUnpaid = $billType->bills->where('status', Bill::STATUS_UNPAID)->sum('amount');

    //     $billType->total_bill = $totalBill ?? 0;
    //     $billType->total_paid = $totalPaid ?? 0;
    //     $billType->total_unpaid = $totalUnpaid ?? 0;

    //     $bills = Bill::with('transactions')->where('student_id', $requestData['student_id'])
    //         ->where('bill_type_id', $requestData['bill_type_id'])
    //         ->orderBy('month', 'asc')
    //         ->get();


    //     $paymentMethods = PaymentMethod::latest()->get();

    //     if (request()->ajax()) { return view('admins.bill.summary-ajax', compact('student', 'billType', 'bills', 'summary', 'paymentMethods')); }
        return view('admins.bill.summary', compact('student', 'billType', 'bills', 'paymentMethods'));
    // }

    public function changeStatus()
    {
        $user = Auth::user();
        $isAuthorized = false;

        if ($user) {
            if ($user->hasRole('Super Admin') || $user->hasRole('Bendahara') || $user->can('Edit Status Tagihan')) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $requestData = request()->only(['bill_id', 'status']);

        DB::beginTransaction();
        try {
            $bill = Bill::findOrFail($requestData['bill_id']);
            $oldStatus = $bill->status;
            $newStatus = $requestData['status'];

            if ($oldStatus != $newStatus) {
                $bill->status = $newStatus;
                if ($newStatus == Bill::STATUS_PAID) {
                    $bill->paid_amount = $bill->amount;
                } else {
                    $bill->paid_amount = 0;
                }
                $bill->save();

                // Hapus atau batalkan transaksi jika status dikembalikan ke UNPAID
                if ($newStatus == Bill::STATUS_UNPAID) {
                    $transactionDetails = $bill->transactionDetails;
                    foreach ($transactionDetails as $detail) {
                        $transaction = $detail->transaction;
                        if ($transaction) {
                            // Jika pembayaran menggunakan Saldo, kembalikan saldo siswa (Refund)
                            if ($transaction->paymentMethod?->type == \App\Models\PaymentMethod::TYPE_BALANCE || $detail->saldo_history_id) {
                                $student = \App\Models\Student::where('id', $transaction->student_id)->lockForUpdate()->first();
                                if ($student) {
                                    $refundAmount = $detail->amount ?? $bill->amount;
                                    $balanceBefore = $student->saldo;
                                    $student->increment('saldo', $refundAmount);
                                    $student->refresh();
                                    $balanceAfter = $student->saldo;

                                    // Catat riwayat refund saldo
                                    \App\Models\SaldoHistory::create([
                                        'student_id' => $student->id,
                                        'amount' => $refundAmount,
                                        'type' => \App\Models\SaldoHistory::TYPE_IN,
                                        'description' => 'Refund Pembatalan Tagihan ' . ($bill->billType?->name ?? '') . ' (' . ($transaction->payment_code ?? '') . ') Sebesar Rp.' . number_format($refundAmount, 0, ',', '.'),
                                        'status' => \App\Models\SaldoHistory::STATUS_SUCCESS,
                                        'usage' => \App\Models\SaldoHistory::USAGE_TOPUP,
                                        'balance_before' => $balanceBefore,
                                        'balance_after' => $balanceAfter,
                                    ]);

                                    \App\Services\SaldoRecalculatorService::recalculateForStudent($student->id);
                                }
                            }

                            // Batalkan transaksi dan soft-delete detail untuk menjaga audit trail
                            $transaction->update(['status' => \App\Models\Transaction::STATUS_CANCELLED]);
                            $detail->delete();
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Status tagihan berhasil diubah');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return redirect()->back()->with('error', 'Gagal mengubah status tagihan: ' . $th->getMessage());
        }
    }

    public function deleteStudentBill(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'bill_type_id' => 'required',
        ]);

        $student = Student::findOrFail($request->student_id);
        $billType = BillType::findOrFail($request->bill_type_id);
        $billType->bills()->where('student_id', $student->id)->delete();

        return redirect()->back()->with('success', 'Tagihan berhasil dihapus');
    }

    public function downloadTemplate(Request $request)
    {
        $schoolId = $request->school_id;
        $classroomIds = $request->classroom_ids ?? ($request->classroom_id ? [$request->classroom_id] : []);
        $academicYearId = $request->academic_year_id;
        $billTypeIds = $request->bill_type_ids ?? ($request->bill_type_id ? [$request->bill_type_id] : []);

        $school = School::find($schoolId);
        $schoolName = $school ? str_replace(' ', '_', $school->name) : 'Semua_UPT';

        $classroomName = 'Semua_Kelas';
        if (!empty($classroomIds)) {
            $classrooms = Classroom::whereIn('id', (array)$classroomIds)->pluck('name')->toArray();
            if (count($classrooms) === 1) {
                $classroomName = str_replace(' ', '_', $classrooms[0]);
            } elseif (count($classrooms) > 1) {
                $classroomName = count($classrooms) . '_Kelas';
            }
        }

        $fileName = "Template_Pembayaran_{$schoolName}_{$classroomName}.xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\StudentBillTemplateExport($schoolId, $classroomIds, $academicYearId, $billTypeIds),
            $fileName
        );
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    if (!$value || !$value->isValid()) {
                        return $fail('File yang diunggah tidak valid.');
                    }
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
                        return $fail('File harus berupa berkas berjenis: xls, xlsx.');
                    }
                },
            ],
            'academic_year_id' => 'required|exists:academic_years,id',
            'bill_type_id' => 'required|exists:bill_types,id',
        ]);

        $file = $request->file('file');
        $academicYearId = $request->academic_year_id;
        $billTypeId = $request->bill_type_id;

        $billType = BillType::with('academicYear')->where('id', $billTypeId)->firstOrFail();

        if ($billType->academic_year_id && $billType->academic_year_id !== $academicYearId) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis tagihan yang dipilih tidak sesuai dengan Tahun Ajaran yang dipilih.',
            ], 422);
        }

        $academicYearName = $billType->academicYear?->name ?? \App\Models\AcademicYear::find($academicYearId)?->name;

        // Baca file Excel
        $rows = \Maatwebsite\Excel\Facades\Excel::toArray([], $file)[0];

        if (empty($rows) || count($rows) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'File Excel kosong atau tidak memiliki baris data.',
            ], 422);
        }

        $headers = $rows[0];
        $idColIndex = count($headers) - 1;
        foreach ($headers as $idx => $headerName) {
            if (strtolower(trim($headerName ?? '')) === 'id siswa' || strtolower(trim($headerName ?? '')) === 'id_siswa') {
                $idColIndex = $idx;
                break;
            }
        }

        $previewData = [];
        $isValidGlobal = true;

        $paymentColIndices = [];
        $colMap = [];
        $monthNames = ['juli' => 7, 'agustus' => 8, 'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12, 'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4, 'mei' => 5, 'juni' => 6];

        for ($c = 3; $c < $idColIndex; $c++) {
            $paymentColIndices[] = $c;
            $headerText = strtolower(trim($headers[$c] ?? ''));
            $matchedMonth = null;
            $matchedYear = null;
            
            if ($billType->type === BillType::TYPE_MONTHLY) {
                foreach ($monthNames as $mName => $mNum) {
                    if (str_contains($headerText, $mName)) {
                        $matchedMonth = $mNum;
                        preg_match('/\b(20\d{2})\b/', $headerText, $matches);
                        if (!empty($matches[1])) {
                            $matchedYear = intval($matches[1]);
                        }
                        break;
                    }
                }
            }
            $colMap[$c] = [
                'month' => $matchedMonth,
                'year' => $matchedYear,
            ];
        }
        
        if (empty($paymentColIndices)) {
            $paymentColIndices = [3];
            $colMap[3] = ['month' => null, 'year' => null];
        }

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $name = $row[1] ?? '';
            $className = $row[2] ?? '';
            $studentId = $row[$idColIndex] ?? null;

            if (empty($name) && empty($studentId)) {
                continue; // Skip baris kosong
            }

            $student = null;
            $status = 'VALID';
            $message = '';
            
            if ($studentId) {
                $student = Student::with('classroom')->find($studentId);
            }

            if (!$student && $name) {
                $student = Student::where('name', 'like', $name)
                    ->whereHas('classroom', function($q) use ($className) {
                        $q->where('name', 'like', $className);
                    })
                    ->first();
            }

            if (!$student) {
                $previewData[] = [
                    'student_id' => null,
                    'name' => $name,
                    'classroom' => $className,
                    'amount' => 0,
                    'status' => 'INVALID',
                    'message' => 'Siswa tidak ditemukan',
                    'allocations' => []
                ];
                $isValidGlobal = false;
                continue;
            }

            $studentBills = Bill::where('student_id', $student->id)
                ->where('bill_type_id', $billTypeId)
                ->where('academic_year_id', $academicYearId)
                ->get();
            
            if ($billType->type === BillType::TYPE_MONTHLY) {
                $mOrder = [7=>1, 8=>2, 9=>3, 10=>4, 11=>5, 12=>6, 1=>7, 2=>8, 3=>9, 4=>10, 5=>11, 6=>12];
                $sortedBills = $studentBills->sortBy(function($b) use ($mOrder) {
                    $orderIndex = $mOrder[$b->month] ?? $b->month;
                    return ($b->year * 100) + $orderIndex;
                });
            } else {
                $sortedBills = $studentBills;
            }
            $unpaidBills = $sortedBills->filter(fn($b) => $b->status === Bill::STATUS_UNPAID || (int)$b->paid_amount < (int)$b->amount);

            $validAmount = 0;
            $allocations = [];
            
            $isMonthly = $billType->type === BillType::TYPE_MONTHLY;
            $nonMonthlyTotal = 0;

            foreach ($paymentColIndices as $colIdx) {
                $rawVal = $row[$colIdx] ?? 0;
                $cellVal = is_string($rawVal) ? intval(preg_replace('/[^0-9]/', '', $rawVal)) : intval($rawVal);
                
                if ($cellVal <= 0) continue;

                $map = $colMap[$colIdx];
                if ($isMonthly && $map['month']) {
                    $specificBill = $studentBills->first(function($b) use ($map) {
                        return $b->month == $map['month'] && (!$map['year'] || $b->year == $map['year']);
                    });

                    if ($specificBill) {
                        $isUnpaid = $specificBill->status === Bill::STATUS_UNPAID || (int)$specificBill->paid_amount < (int)$specificBill->amount;
                        if ($isUnpaid) {
                            $needed = (int)$specificBill->amount - (int)$specificBill->paid_amount;
                            $allocated = min($cellVal, $needed);
                            if ($allocated > 0) {
                                $allocations[] = ['bill_id' => $specificBill->id, 'amount' => $allocated];
                                $validAmount += $allocated;
                            }
                        }
                    }
                } else {
                    $nonMonthlyTotal += $cellVal;
                }
            }
            
            // Allocate non-monthly (or unmapped) amounts chronologically
            if ($nonMonthlyTotal > 0) {
                if (!$isMonthly) {
                    // Logika Bisnis Pendaftaran/Bebas (Cumulative):
                    // Excel menyimpan "Total Pembayaran Terkini" (Cumulative).
                    // Maka, kurangi total di Excel dengan total yang sudah dibayar di sistem.
                    $totalPaidInDb = $studentBills->sum(function($b) {
                        return (int) $b->getRawOriginal('paid_amount');
                    });
                    $remainingToAllocate = max(0, $nonMonthlyTotal - $totalPaidInDb);
                } else {
                    $remainingToAllocate = $nonMonthlyTotal;
                }

                foreach ($unpaidBills as $bill) {
                    // Skip if already allocated in specific columns
                    $alreadyAllocated = collect($allocations)->where('bill_id', $bill->id)->sum('amount');
                    $needed = ((int)$bill->amount - (int)$bill->getRawOriginal('paid_amount')) - $alreadyAllocated;
                    
                    if ($needed > 0 && $remainingToAllocate > 0) {
                        $allocated = min($remainingToAllocate, $needed);
                        
                        $existingIndex = null;
                        foreach ($allocations as $idx => $alloc) {
                            if ($alloc['bill_id'] == $bill->id) {
                                $existingIndex = $idx;
                                break;
                            }
                        }
                        
                        if ($existingIndex !== null) {
                            $allocations[$existingIndex]['amount'] += $allocated;
                        } else {
                            $allocations[] = ['bill_id' => $bill->id, 'amount' => $allocated];
                        }
                        
                        $validAmount += $allocated;
                        $remainingToAllocate -= $allocated;
                    }
                }
            }

            if ($validAmount <= 0) {
                $status = 'SKIPPED';
                $message = 'Nominal kosong / Tagihan sudah lunas (Diabaikan)';
            }

            $previewData[] = [
                'student_id' => $student->id,
                'name' => $student->name,
                'classroom' => $student->classroom ? $student->classroom->name : $className,
                'amount' => $validAmount,
                'status' => $status,
                'message' => $message,
                'allocations' => $allocations
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $previewData,
            'is_valid_global' => $isValidGlobal,
            'bill_type_name' => $billType->formatted_name ?? $billType->name,
            'academic_year_name' => $academicYearName,
        ]);
    }

    public function confirmImport(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'bill_type_id' => 'required|exists:bill_types,id',
            'data' => 'required|array',
            'data.*.student_id' => 'required|exists:students,id',
            'data.*.amount' => 'required|integer|min:1',
        ]);

        $academicYearId = $request->academic_year_id;
        $billTypeId = $request->bill_type_id;
        $billType = BillType::where('id', $billTypeId)->firstOrFail();

        if ($billType->academic_year_id && $billType->academic_year_id !== $academicYearId) {
            return response()->json([
                'success' => false,
                'message' => 'Jenis tagihan yang dipilih tidak sesuai dengan Tahun Ajaran yang dipilih.',
            ], 422);
        }

        $importedData = $request->data;
        $adminId = Auth::id();

        $successCount = 0;
        $failedCount = 0;

        DB::beginTransaction();
        try {
            $importLog = ImportLog::create([
                'admin_id' => $adminId,
                'school_id' => $request->school_id ?? null,
                'classroom_info' => $request->classroom_info ?? null,
                'academic_year_id' => $academicYearId,
                'bill_type_id' => $billTypeId,
                'total_students' => 0,
                'total_amount' => 0,
                'status' => ImportLog::STATUS_ACTIVE,
            ]);

            $paymentMethod = PaymentMethod::where('type', PaymentMethod::TYPE_CASH)->first();
            $paymentMethodId = $paymentMethod ? $paymentMethod->id : PaymentMethod::CASH_PAYMENT;

            foreach ($importedData as $item) {
                $studentId = $item['student_id'];
                $amount = intval($item['amount']);

                if ($amount <= 0) {
                    $failedCount++;
                    continue;
                }

                $student = Student::with('classroom')->findOrFail($studentId);

                $studentBills = Bill::where('student_id', $studentId)
                    ->where('bill_type_id', $billTypeId)
                    ->where('academic_year_id', $academicYearId)
                    ->get();

                // Sort bills chronologically if monthly (July to June order)
                if ($billType->type === BillType::TYPE_MONTHLY) {
                    $mOrder = [7=>1, 8=>2, 9=>3, 10=>4, 11=>5, 12=>6, 1=>7, 2=>8, 3=>9, 4=>10, 5=>11, 6=>12];
                    $sortedBills = $studentBills->sortBy(function($b) use ($mOrder) {
                        $orderIndex = $mOrder[$b->month] ?? $b->month;
                        return ($b->year * 100) + $orderIndex;
                    });
                } else {
                    $sortedBills = $studentBills;
                }

                $unpaidBills = $sortedBills->filter(fn($b) => $b->status === Bill::STATUS_UNPAID || (int)$b->paid_amount < (int)$b->amount);

                if ($studentBills->count() > 0 && $unpaidBills->isEmpty()) {
                    $failedCount++;
                    continue; // All bills already paid
                }

                $transactionCount = Transaction::whereDate('created_at', now())->count();
                $paymentCode = 'CHT-IMP-' . now()->format('Ymd') . str_pad($transactionCount + 1, 4, '0', STR_PAD_LEFT);

                $transaction = Transaction::create([
                    'pay_amount' => $amount,
                    'payment_code' => $paymentCode,
                    'student_id' => $studentId,
                    'expiry_time' => now()->addMinutes(1440),
                    'status' => Transaction::STATUS_PAID,
                    'paid_at' => now(),
                    'type' => Transaction::TYPE_BILL,
                    'admin_id' => $adminId,
                    'payment_method_id' => $paymentMethodId,
                    'import_log_id' => $importLog->id,
                ]);

                $allocations = $item['allocations'] ?? [];
                
                if (!empty($allocations) && is_array($allocations)) {
                    // Use specific allocations provided by frontend
                    foreach ($allocations as $alloc) {
                        $billId = $alloc['bill_id'];
                        $allocAmount = (int)$alloc['amount'];
                        
                        if ($allocAmount <= 0) continue;
                        
                        $bill = $studentBills->firstWhere('id', $billId);
                        if ($bill) {
                            $needed = max(0, $bill->amount - (int)$bill->paid_amount);
                            $payAmount = min($allocAmount, $needed);
                            
                            if ($payAmount > 0) {
                                \App\Models\TransactionDetail::create([
                                    'transaction_id' => $transaction->id,
                                    'bill_id' => $bill->id,
                                    'amount' => $payAmount,
                                ]);
                                
                                $bill->paid_amount = (int)$bill->paid_amount + $payAmount;
                                if ($bill->paid_amount >= $bill->amount) {
                                    $bill->status = Bill::STATUS_PAID;
                                }
                                $bill->save();
                            }
                        }
                    }
                } else {
                    // Fallback to chronological allocation if allocations are not provided
                    $remainingToAllocate = $amount;
                    if ($unpaidBills->isNotEmpty()) {
                        foreach ($unpaidBills as $bill) {
                            if ($remainingToAllocate <= 0) {
                                break;
                            }

                            $needed = max(0, $bill->amount - (int)$bill->paid_amount);
                            $payAmount = min($remainingToAllocate, $needed);

                            \App\Models\TransactionDetail::create([
                                'transaction_id' => $transaction->id,
                                'bill_id' => $bill->id,
                                'amount' => $payAmount,
                            ]);

                            $bill->paid_amount = (int)$bill->paid_amount + $payAmount;
                            if ($bill->paid_amount >= $bill->amount) {
                                $bill->status = Bill::STATUS_PAID;
                            }
                            $bill->save();

                            $remainingToAllocate -= $payAmount;
                        }
                        
                        // Excess payment handling
                        if ($remainingToAllocate > 0) {
                            $lastBill = $unpaidBills->last();
                            if ($lastBill) {
                                $lastDetail = \App\Models\TransactionDetail::where('transaction_id', $transaction->id)->where('bill_id', $lastBill->id)->first();
                                if ($lastDetail) {
                                    $lastDetail->amount += $remainingToAllocate;
                                    $lastDetail->save();
                                }
                                $lastBill->paid_amount += $remainingToAllocate;
                                $lastBill->save();
                            }
                        }
                    } else {
                        // Create on the fly if no bills existed
                        $bill = Bill::create([
                            'bill_type_id' => $billTypeId,
                            'student_id' => $studentId,
                            'classroom_id' => $student->classroom_id ?? '',
                            'academic_year_id' => $academicYearId,
                            'month' => intval(date('m')),
                            'year' => intval(date('Y')),
                            'amount' => $amount,
                            'paid_amount' => $amount,
                            'status' => Bill::STATUS_PAID,
                        ]);

                        \App\Models\TransactionDetail::create([
                            'transaction_id' => $transaction->id,
                            'bill_id' => $bill->id,
                            'amount' => $amount,
                        ]);
                    }
                }

                \App\Services\TransactionService::handleUnitTransferIfApplicable($transaction);

                try {
                    if ($student->user && $student->user->phone) {
                        \App\Services\TransactionService::dispatchNotifications($transaction);
                    }
                } catch (\Exception $e) {
                    Log::warning("Gagal mengirim WA notifikasi import: " . $e->getMessage());
                }

                $successCount++;
            }

            $importLog->update([
                'total_students' => $successCount,
                'total_amount' => collect($importedData)->sum(function($item) { return intval($item['amount']); }),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengimport {$successCount} data pembayaran tagihan.",
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'import_log_id' => $importLog->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal melakukan import: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Gagal memproses import: " . $e->getMessage(),
            ], 500);
        }
    }

    public function getImportLogs()
    {
        $data = ImportLog::with(['admin', 'school', 'academicYear', 'billType.billItem', 'rolledBackByAdmin'])
            ->latest()
            ->get();

        return \Yajra\DataTables\DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('school_name', function ($row) {
                $schoolName = $row->school->name ?? '-';
                $classroomInfo = $row->classroom_info;
                if ($classroomInfo) {
                    return '<span class="fw-bold">' . e($schoolName) . '</span>'
                        . '<br><small class="text-muted fst-italic">' . e($classroomInfo) . '</small>';
                }
                return e($schoolName);
            })
            ->addColumn('academic_year_name', function ($row) {
                return $row->academicYear->name ?? '-';
            })
            ->addColumn('bill_type_name', function ($row) {
                $name = $row->billType->name ?? '-';
                $billItem = $row->billType->billItem->name ?? '';
                if ($billItem && !str_contains(strtolower($name), strtolower($billItem))) {
                    $name .= ' - ' . $billItem;
                }
                return $name;
            })
            ->addColumn('admin_name', function ($row) {
                return $row->admin->name ?? '-';
            })
            ->addColumn('total_amount_formatted', function ($row) {
                return 'Rp ' . number_format($row->total_amount, 0, ',', '.');
            })
            ->addColumn('timestamp', function ($row) {
                return $row->created_at ? $row->created_at->format('d/m/Y H:i') : '-';
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->status === ImportLog::STATUS_ACTIVE) {
                    return '<span class="badge badge-light-success fw-bold">Aktif</span>';
                }
                $rolledBackBy = $row->rolledBackByAdmin->name ?? '';
                $rolledBackAt = $row->rolled_back_at ? $row->rolled_back_at->format('d/m/Y H:i') : '';
                return '<span class="badge badge-light-danger fw-bold">Dibatalkan</span>'
                    . '<br><small class="text-muted fst-italic">' . $rolledBackBy . ' - ' . $rolledBackAt . '</small>';
            })
            ->addColumn('action', function ($row) {
                if ($row->status === ImportLog::STATUS_ACTIVE) {
                    return '<button class="btn btn-sm btn-light-danger fw-bold btn-rollback-import" data-id="' . $row->id . '" style="border-radius: 8px;">'
                        . '<i class="fas fa-undo me-1"></i> Batal'
                        . '</button>';
                }
                return '<span class="text-muted fst-italic fs-8">-</span>';
            })
            ->rawColumns(['school_name', 'status_badge', 'action'])
            ->make(true);
    }

    public function getImportLogDetails($id)
    {
        $importLog = ImportLog::findOrFail($id);
        
        $transactions = Transaction::with(['student.classroom'])
            ->where('import_log_id', $importLog->id)
            ->get();

        $details = $transactions->map(function ($tx) {
            return [
                'student_name' => $tx->student->name ?? '-',
                'classroom' => $tx->student->classroom->name ?? '-',
                'amount_formatted' => 'Rp ' . number_format($tx->pay_amount, 0, ',', '.')
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $details
        ]);
    }

    public function rollbackImport($id)
    {
        $importLog = ImportLog::where('id', $id)
            ->where('status', ImportLog::STATUS_ACTIVE)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $transactions = Transaction::where('import_log_id', $importLog->id)
                ->with('transactionDetails.bill')
                ->get();

            foreach ($transactions as $transaction) {
                foreach ($transaction->transactionDetails as $detail) {
                    if ($detail->bill) {
                        $bill = $detail->bill;
                        // Retrieve raw amount to avoid accessor interference
                        $currentPaid = $bill->getRawOriginal('paid_amount');
                        $newPaid = max(0, (int)$currentPaid - (int)$detail->amount);
                        
                        if ($newPaid == 0) {
                            $newStatus = 'UNPAID';
                        } elseif ($newPaid >= $bill->amount) {
                            $newStatus = 'PAID';
                        } else {
                            $newStatus = 'PARTIAL';
                        }
                        
                        // Set status first so the accessor doesn't force paid_amount to amount
                        $bill->status = $newStatus;
                        $bill->paid_amount = $newPaid;
                        $bill->save();
                    }
                    // Soft-delete detail
                    $detail->delete();
                }
                // Soft-delete transaction
                $transaction->status = Transaction::STATUS_CANCELLED;
                $transaction->save();
                $transaction->delete();
            }

            // Update import log
            $importLog->update([
                'status' => ImportLog::STATUS_ROLLED_BACK,
                'rolled_back_at' => now(),
                'rolled_back_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Import berhasil dibatalkan. ' . $transactions->count() . ' transaksi telah di-rollback.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rollback import gagal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan rollback: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tampilkan hasil audit konsistensi 6 item tagihan dan keuangan.
     */
    public function auditConsistency()
    {
        $user = Auth::user();
        $isAuthorized = $user && (
            $user->can('Manage Tagihan') ||
            $user->can('Edit Status Tagihan') ||
            $user->hasRole('SUPER ADMIN') ||
            $user->hasRole('Super Admin') ||
            str_contains(strtoupper($user->getRoleNames()->implode(' ')), 'BENDAHARA')
        );

        if (!$isAuthorized) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses ke fitur audit konsistensi.');
        }

        $auditResults = \App\Services\BillingConsistencyAuditService::runFullAudit();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $auditResults
            ]);
        }

        return view('admins.bill.audit-consistency', compact('auditResults'));
    }

    /**
     * Eksekusi perbaikan otomatis konsistensi data tagihan.
     */
    public function repairConsistency(Request $request)
    {
        $user = Auth::user();
        $isAuthorized = $user && (
            $user->can('Manage Tagihan') ||
            $user->can('Edit Status Tagihan') ||
            $user->hasRole('SUPER ADMIN') ||
            $user->hasRole('Super Admin') ||
            str_contains(strtoupper($user->getRoleNames()->implode(' ')), 'BENDAHARA')
        );

        if (!$isAuthorized) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $options = [
            'fix_overpaid' => $request->boolean('fix_overpaid', true),
            'fix_ghost_inactive' => $request->boolean('fix_ghost_inactive', false),
            'fix_ghost_deleted' => $request->boolean('fix_ghost_deleted', false),
            'relink_rate_items' => $request->boolean('relink_rate_items', true),
            'backfill_details' => $request->boolean('backfill_details', true),
        ];

        $dryRun = $request->boolean('dry_run', false);

        DB::beginTransaction();
        try {
            $repairResults = \App\Services\BillingConsistencyAuditService::repair($options, $dryRun);

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }

            return response()->json([
                'status' => 'success',
                'message' => $dryRun ? 'Simulasi perbaikan (dry-run) berhasil diselesaikan tanpa mengubah database.' : 'Perbaikan konsistensi tagihan berhasil disimpan ke database.',
                'results' => $repairResults,
                'dry_run' => $dryRun
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}


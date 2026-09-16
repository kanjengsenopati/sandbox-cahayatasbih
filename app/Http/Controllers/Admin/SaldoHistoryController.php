<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Contact;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\School;
use App\Models\Transaction;
use App\Models\SaldoHistory;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Yajra\DataTables\DataTables;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use App\Imports\SaldoHistoryImport;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\SendNotifWaService;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use App\Jobs\SendToPushNotificationJob;
use App\Jobs\SendToWhatsappNotificationJob;
use App\Http\Requests\Admin\SaldoHistoryRequest;
use App\Http\Requests\Admin\UpdateStatusTopupSaldoRequest;

class SaldoHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $hasAccess = false;
        if ($user) {
            try {
                if ($user->can('Manage Saldo Santri') || $user->can('Create Saldo Santri') || (method_exists($user, 'isKoordinatorCahayaMart') && $user->isKoordinatorCahayaMart())) {
                    $hasAccess = true;
                }
            } catch (\Throwable $e) {}

            try {
                if (method_exists($user, 'hasRole') && ($user->hasRole('Super Admin') || $user->hasRole('Admin'))) {
                    $hasAccess = true;
                }
            } catch (\Throwable $e) {}

            if (!$hasAccess && Auth::guard('web')->check()) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            return redirect()->route('dashboard')->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax() && request()->type === 'archive') {
            return $this->getArchiveTransactionData();
        }
        if (request()->ajax() && request()->type === 'saldo') {
            $data = SaldoHistory::with(['student.classroom.school', 'outlet'])->hasSchool();

            if ($schoolId = request()->school_id) {
                $data->whereHas('student.classroom', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            }
            if ($classroomId = request()->classroom_id) {
                $data->whereHas('student', function ($q) use ($classroomId) {
                    $q->where('classroom_id', $classroomId);
                });
            }

            if ($searchName = request()->search_name) {
                $data->whereHas('student', function ($q) use ($searchName) {
                    $q->where('name', 'like', "%{$searchName}%")
                      ->orWhere('nis', 'like', "%{$searchName}%")
                      ->orWhere('nisn', 'like', "%{$searchName}%");
                });
            }
            $startDate = request()->filled('start_date') ? request()->start_date : now()->subDays(6)->format('Y-m-d');
            $endDate = request()->filled('end_date') ? request()->end_date : now()->format('Y-m-d');
            if ($startDate) {
                $data->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $data->whereDate('created_at', '<=', $endDate);
            }

            // Ensure stable sort order matching the recalculator logic
            $data->orderBy('created_at', 'desc')->orderBy('id', 'desc');

            return DataTables::of($data)
                ->addColumn('date', function ($data) {
                    return $data->created_at ? $data->created_at->translatedFormat('d F Y' . ' <br>' . 'H:i:s') : '-';
                })
                ->editColumn('amount', function ($data) {
                    $formattedAmount = 'Rp ' . number_format($data->amount, 0, ',', '.');

                    if ($data->type === 'IN') {
                        return '<span class="badge bg-light-success text-success fw-bolder px-3 py-2 fs-7">+' . $formattedAmount . '</span>';
                    } else {
                        return '<span class="badge bg-light-danger text-danger fw-bolder px-3 py-2 fs-7">-' . $formattedAmount . '</span>';
                    }
                })
                ->editColumn('balance_before', function ($data) {
                    $val = (float)($data->balance_before ?? 0);
                    $badgeClass = $val < 0 ? 'bg-light-danger text-danger' : 'bg-light-success text-success';
                    return '<span class="badge ' . $badgeClass . ' fw-bold px-3 py-2 fs-7">Rp ' . number_format($val, 0, ',', '.') . '</span>';
                })
                ->editColumn('balance_after', function ($data) {
                    $val = (float)($data->balance_after ?? 0);
                    $badgeClass = $val < 0 ? 'bg-light-danger text-danger' : 'bg-light-success text-success';
                    return '<span class="badge ' . $badgeClass . ' fw-bold px-3 py-2 fs-7">Rp ' . number_format($val, 0, ',', '.') . '</span>';
                })
                ->editColumn('status', function ($data) {
                    if ($data->status === SaldoHistory::STATUS_SUCCESS) {
                        return '<span class="badge bg-success fw-bold px-3 py-2 fs-7">' . $data->status . '</span>';
                    } elseif ($data->status === SaldoHistory::STATUS_PENDING) {
                        return '<span class="badge bg-warning text-dark fw-bold px-3 py-2 fs-7">' . $data->status . '</span>';
                    } else {
                        return '<span class="badge bg-danger fw-bold px-3 py-2 fs-7">' . $data->status . '</span>';
                    }
                })
                ->editColumn('description', function ($data) {
                    $desc = e($data->description ?? '-');
                    $icon = '<i class="fas fa-receipt text-gray-400 me-2"></i>';
                    if ($data->type === 'IN') {
                        $icon = '<i class="fas fa-arrow-down text-success me-2"></i>';
                    } elseif ($data->type === 'OUT') {
                        $icon = '<i class="fas fa-shopping-cart text-danger me-2"></i>';
                    } elseif ($data->type === 'WITHDRAW') {
                        $icon = '<i class="fas fa-hand-holding-usd text-warning me-2"></i>';
                    }
                    return '<div class="d-flex align-items-center fs-7 text-gray-800">' . $icon . '<span>' . $desc . '</span></div>';
                })
                ->addColumn('action', function ($row) {
                    if (Auth::user()->hasRole('Super Admin')) {
                        return '<button class="btn btn-danger btn-sm delete-history-btn" data-id="' . $row->id . '">
                                    <i class="fas fa-trash me-1"></i> Hapus
                                </button>';
                    }
                    return '-';
                })
                ->rawColumns(['amount', 'status', 'date', 'balance_before', 'balance_after', 'description', 'action'])
                ->make(true);
        }
        if (request()->ajax() && request()->type === 'topup') {
            $transactions = Transaction::with(['student.classroom.school.saldoBank.bank', 'paymentMethod', 'activeProof.bank', 'transactionProofs.bank'])
                ->where('type', Transaction::TYPE_SALDO)
                ->where(function ($q) {
                    $q->where('status', Transaction::STATUS_PENDING_CONFIRMATION)
                      ->orWhere(function ($sq) {
                          $sq->where('status', Transaction::STATUS_PENDING_PAYMENT)
                             ->whereHas('transactionProofs', function ($pq) {
                                 $pq->whereNotNull('proof_image');
                             });
                      });
                })
                ->hasSchool()
                ->latest();

            if ($schoolId = request()->school_id) {
                $transactions->whereHas('student.classroom', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            }
            if ($classroomId = request()->classroom_id) {
                $transactions->whereHas('student', function ($q) use ($classroomId) {
                    $q->where('classroom_id', $classroomId);
                });
            }

            if ($searchName = request()->search_name) {
                $transactions->whereHas('student', function ($q) use ($searchName) {
                    $q->where('name', 'like', "%{$searchName}%")
                      ->orWhere('nis', 'like', "%{$searchName}%")
                      ->orWhere('nisn', 'like', "%{$searchName}%");
                });
            }

            if (request()->filled('start_date')) {
                $transactions->whereDate('created_at', '>=', request()->start_date);
            }
            if (request()->filled('end_date')) {
                $transactions->whereDate('created_at', '<=', request()->end_date);
            }

            return DataTables::of($transactions)
                ->addColumn('proof', function ($transaction) {
                    $proof = $transaction->activeProof ?? $transaction->transactionProofs->first();
                    $proofUrl = $proof?->proof_image_url ?? $proof?->proof_image;
                    if (!$proofUrl) return '<span class="text-muted fs-8 fst-italic">Belum upload</span>';
                    $fallbackSvg = "data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' viewBox=\\'0 0 100 100\\'%3E%3Crect width=\\'100\\' height=\\'100\\' fill=\\'%23f1f5f9\\'/%3E%3Ctext x=\\'50%25\\' y=\\'50%25\\' dominant-baseline=\\'middle\\' text-anchor=\\'middle\\' font-family=\\'sans-serif\\' font-size=\\'11\\' fill=\\'%2394a3b8\\'%3ETidak Ada%3C/text%3E%3C/svg%3E";
                    return "<img src='{$proofUrl}' class='img-fluid img-thumbnail cursor-pointer view-proof-image shadow-sm' data-src='{$proofUrl}' onerror=\"this.onerror=null; this.src='{$fallbackSvg}';\" style='max-width: 75px; max-height: 75px; object-fit: cover; border-radius: 8px;' alt='Bukti Transfer' title='Klik untuk melihat bukti full'>";
                })
                ->editColumn('pay_amount', function ($transaction) {
                    return '<span class="badge bg-light-success text-success fw-bolder fs-6 px-3 py-2">Rp ' . number_format($transaction->pay_amount ?? 0, 0, ',', '.') . '</span>';
                })
                ->editColumn('unique_payment', function ($transaction) {
                    if ($transaction->unique_payment) {
                        return '<span class="badge bg-light-primary text-primary font-monospace fw-bold px-2 py-1 fs-7">#' . e($transaction->unique_payment) . '</span>';
                    }
                    return '<span class="text-muted fs-8 font-monospace">-</span>';
                })
                ->editColumn('status', function ($transaction) {
                    $statusHtml = '<span class="badge badge-warning text-dark fw-bolder px-3 py-2 fs-7 shadow-xs">Verifikasi Petugas</span>';

                    if ($transaction->created_at) {
                        $formattedDate = strtoupper(\Carbon\Carbon::parse($transaction->created_at)->translatedFormat('d-M-Y , H : i'));
                        $statusHtml .= "<br><div class='text-slate-400 mt-1' style='font-size: 11px; font-style: italic; color: #94a3b8;'>{$formattedDate}</div>";
                    }

                    return $statusHtml;
                })
                ->addColumn('action', function ($transaction) {
                    if (Auth::user()->can('Edit Saldo Santri') || Auth::user()->can('Manage Saldo Santri') || (method_exists(Auth::user(), 'isKoordinatorCahayaMart') && Auth::user()->isKoordinatorCahayaMart())) {
                        if ($transaction->status == Transaction::STATUS_PAID) {
                            return "<span class='badge badge-success'>Lunas</span>";
                        } elseif ($transaction->status == Transaction::STATUS_REJECTED) {
                            return "<span class='badge badge-danger'>Ditolak</span>";
                        }

                        $action = "<div class='d-flex flex-column gap-1 align-items-center justify-content-center'>
                            <select class='form-select form-select-sm status-transaction' name='status' id='status-{$transaction->id}' onchange='updateStatus(this.value, \"{$transaction->id}\")' style='width: 130px;'>
                                <option value=''>Pilih Status</option>
                                <option value='" . Transaction::STATUS_PAID . "' " . ($transaction->status == Transaction::STATUS_PAID ? 'selected' : '') . ">Lunas</option>
                                <option value='" . Transaction::STATUS_REJECTED . "' " . ($transaction->status == Transaction::STATUS_REJECTED ? 'selected' : '') . ">Ditolak</option>
                            </select>
                            <input type='hidden' name='note' id='note-{$transaction->id}' value='" . e($transaction->activeProof?->note ?? '') . "'>
                            <button class='btn btn-primary btn-sm px-3 py-1 mt-1' onclick='saveStatus(\"{$transaction->id}\")'>Simpan</button>
                        </div>";

                        return $action;
                    }
                    return '-';
                })
                ->addColumn('bank_recipient', function ($transaction) {
                    $proof = $transaction->activeProof ?? $transaction->transactionProofs->first();
                    $bank = $proof?->bank;
                    if (!$bank) {
                        $banks = $transaction->banks;
                        $bank = $banks->first();
                    }
                    if (!$bank) return '-';
                    return "<div class='d-flex flex-column align-items-start'>
                        <span class='fw-bolder text-gray-800 fs-7'>" . e($bank->name) . "</span>
                        <span class='text-muted fs-8'>No: " . e($bank->account_number) . "</span>
                        <span class='text-muted fs-8'>A.N: " . e($bank->account_name) . "</span>
                    </div>";
                })
                ->rawColumns(['proof', 'action', 'status', 'bank_recipient', 'pay_amount', 'unique_payment'])
                ->make(true);
        }
        if (request()->ajax() && request()->type === 'adjust') {
            $students = Student::with(['classroom', 'latestSaldoHistory'])->hasSchool()
                ->when(request('classroom_id'), function ($query, $classroomId) {
                    $query->where('classroom_id', $classroomId);
                });

            return DataTables::of($students)
                ->addColumn('nis', function ($student) {
                    return $student->nis ?? $student->nisn ?? '-';
                })
                ->filterColumn('nis', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('nis', 'like', "%{$keyword}%")
                          ->orWhere('nisn', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('nis', function ($query, $order) {
                    $query->orderBy(DB::raw('COALESCE(students.nis, students.nisn)'), $order);
                })
                ->orderColumn('name', function ($query, $order) {
                    $query->orderBy('students.name', $order);
                })
                ->addColumn('classroom', function ($student) {
                    return $student->classroom->name ?? 'Belum ada kelas';
                })
                ->filterColumn('classroom', function ($query, $keyword) {
                    $query->whereHas('classroom', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('classroom', function ($query, $order) {
                    $query->leftJoin('classrooms', 'students.classroom_id', '=', 'classrooms.id')
                          ->orderBy('classrooms.name', $order)
                          ->select('students.*');
                })
                ->editColumn('saldo', function ($student) {
                    return $student->saldo ?? 0;
                })
                ->addColumn('saldo_awal', function ($student) {
                    return $student->latestSaldoHistory?->balance_before ?? $student->saldo ?? 0;
                })
                ->orderColumn('saldo', function ($query, $order) {
                    $query->orderBy('students.saldo', $order);
                })
                ->orderColumn('saldo_sekarang', function ($query, $order) {
                    $query->orderBy('students.saldo', $order);
                })
                ->addColumn('last_saldo_update_date', function ($student) {
                    $lastUpdate = $student->latestSaldoHistory?->created_at ?? $student->updated_at;
                    return $lastUpdate ? strtoupper($lastUpdate->format('d-M-Y')) : '-';
                })
                ->addColumn('last_saldo_update_time', function ($student) {
                    $lastUpdate = $student->latestSaldoHistory?->created_at ?? $student->updated_at;
                    return $lastUpdate ? $lastUpdate->format('H : i') : '-';
                })
                ->addColumn('status', function ($student) {
                    return $student->status;
                })
                ->addColumn('translated_status', function ($student) {
                    return $student->translated_status;
                })
                ->addColumn('avatar_url', function ($student) {
                    return $student->avatar_url ?? asset('assets/media/avatars/default.png');
                })
                ->make(true);
        }
        $schools = School::orderBy('name')->get();
        $classrooms = Classroom::orderBy('name')->get();
        return view('admins.saldo-history.index', compact('schools', 'classrooms'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(SaldoHistoryRequest $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';

        // Authorization check
        $user = Auth::user();
        $hasAccess = false;
        if ($user) {
            try {
                if ($user->can('Create Saldo Santri') || $user->can('Manage Saldo Santri') || (method_exists($user, 'isKoordinatorCahayaMart') && $user->isKoordinatorCahayaMart())) {
                    $hasAccess = true;
                }
            } catch (\Throwable $e) {}

            try {
                if (method_exists($user, 'hasRole') && ($user->hasRole('Super Admin') || $user->hasRole('Admin'))) {
                    $hasAccess = true;
                }
            } catch (\Throwable $e) {}

            if (!$hasAccess && Auth::guard('web')->check()) {
                $hasAccess = true;
            }
        }

        if (!$hasAccess) {
            if ($isAjax) {
                return response()->json(['code' => 403, 'message' => 'Maaf, Anda tidak memiliki akses untuk aksi tersebut'], 403);
            }
            return redirect()->route('saldo-history.index')->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Clean the request amount by removing thousand separators and commas
        $amount = preg_replace('/[.,]/', '', $request->amount);
        $request->merge(['amount' => $amount]);

        $studentId = $request->student_id;
        $cacheKey = "student_transaction_{$studentId}";

        // Check if there's an active transaction in the cache
        if (Cache::has($cacheKey)) {
            if ($isAjax) {
                return response()->json(['code' => 400, 'message' => 'Transaksi sedang diproses, silakan coba lagi sebentar.'], 400);
            }
            return redirect()->route('saldo-history.index')->with('error', 'Transaksi sedang diproses, silakan coba lagi nanti');
        }

        // Set a cache entry to lock the transaction briefly (15 seconds)
        Cache::put($cacheKey, true, now()->addSeconds(15));

        try {
            DB::beginTransaction();

            // Fetch payment method
            $paymentMethod = PaymentMethod::where('type', PaymentMethod::TYPE_CASH)->first();
            if (!$paymentMethod) {
                $paymentMethod = PaymentMethod::create([
                    'type' => PaymentMethod::TYPE_CASH,
                    'name' => 'Tunai / Cash',
                    'is_active' => true,
                ]);
            }
            $paymentMethodType = $paymentMethod->type;

            // Create transaction
            $transaction = TransactionService::createTransaction($request, $paymentMethodType, Transaction::TYPE_SALDO);

            // Fetch student
            $student = Student::findOrFail($studentId);

            // Handle withdrawal or top-up
            if ($request->type === SaldoHistory::TYPE_WITHDRAW) {
                $this->handleWithdrawal($student, $request, $transaction);
            } else {
                $this->handleTopUp($student, $request, $transaction);
            }

            DB::commit();

            // Send notifications safely
            $saldoHistoryRecord = $transaction->transactionDetails()->first()?->saldoHistory;
            if ($saldoHistoryRecord) {
                $this->sendNotifications($student, $saldoHistoryRecord);
            }

            if ($isAjax) {
                $lastUpdate = $saldoHistoryRecord?->created_at ?? now();
                return response()->json([
                    'code' => 200,
                    'message' => 'Berhasil penyesuaian saldo untuk santri ' . $student->name,
                    'student_id' => $student->id,
                    'new_saldo' => $student->saldo,
                    'formatted_new_saldo' => 'Rp ' . number_format($student->saldo, 0, ',', '.'),
                    'updated_date' => strtoupper($lastUpdate->format('d-M-Y')),
                    'updated_time' => $lastUpdate->format('H : i')
                ]);
            }

            return redirect()->route('saldo-history.index')->with('success', 'Berhasil Topup Saldo');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            if ($isAjax) {
                return response()->json([
                    'code' => 500,
                    'message' => 'Gagal penyesuaian saldo: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('saldo-history.index')->with('error', 'Gagal Topup Saldo: ' . $e->getMessage());
        } finally {
            Cache::forget($cacheKey);
        }
    }

    /**
     * Handle withdrawal logic.
     */
    protected function handleWithdrawal(Student $student, $request, $transaction)
    {
        $amount = (float) $request->amount;
        $balanceBefore = $student->saldo ?? 0;

        // Atomic safe decrement
        $affected = Student::where('id', $student->id)
            ->where('saldo', '>=', $amount)
            ->decrement('saldo', $amount);

        if ($affected === 0) {
            throw new \Exception('Saldo Santri tidak mencukupi');
        }

        $student->refresh();
        $balanceAfter = $student->saldo ?? 0;

        // Create saldo history
        $this->createSaldoHistory($transaction, $request, SaldoHistory::TYPE_WITHDRAW, $balanceBefore, $balanceAfter);
    }

    /**
     * Handle top-up logic.
     */
    protected function handleTopUp(Student $student, $request, $transaction)
    {
        $balanceBefore = $student->saldo ?? 0;

        // Add saldo
        $student->saldo += $request->amount;
        $student->save();

        $balanceAfter = $student->saldo ?? 0;

        // Create saldo history
        $this->createSaldoHistory($transaction, $request, SaldoHistory::TYPE_IN, $balanceBefore, $balanceAfter);
    }

    /**
     * Create saldo history record.
     */
    protected function createSaldoHistory($transaction, $request, $type, $balanceBefore, $balanceAfter)
    {
        $description = $request->description ?? ($type === SaldoHistory::TYPE_WITHDRAW
            ? 'Penarikan Saldo Rp.' . number_format($request->amount, 0, ',', '.') . ' oleh ' . Auth::user()->name
            : 'Topup Saldo Rp.' . number_format($request->amount, 0, ',', '.') . ' oleh ' . Auth::user()->name);

        $saldoHistory = SaldoHistory::create([
            'student_id' => $transaction->student_id,
            'amount' => $request->amount,
            'type' => $type,
            'description' => $description,
            'status' => SaldoHistory::STATUS_SUCCESS,
            'admin_id' => Auth::id(),
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
        ]);

        // Create transaction detail
        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'saldo_history_id' => $saldoHistory->id,
        ]);
    }

    private function sendNotifications($student, $saldoHistory)
    {
        try {
            $activity = ($saldoHistory->type === 'IN') ? 'Topup Saldo' : 'Tarik Saldo';
            $messageWhatsapp = SendNotifWaService::balanceAdjustment($student, $saldoHistory, "SALDO");

            if ($student && $student->user) {
                \App\Services\NotificationService::sendFromTemplate(
                    'balance_update',
                    $student->user,
                    [
                        'student_name' => $student->name,
                        'activity' => $activity,
                        'amount' => number_format($saldoHistory->amount, 0, ',', '.'),
                        'balance' => number_format($student->saldo, 0, ',', '.')
                    ],
                    null
                );
                if ($student->user->phone) {
                    dispatch(new SendToWhatsappNotificationJob($student->user->phone, $messageWhatsapp));
                }
            }

            $contacts = Contact::where('type', Contact::TYPE_BENDAHARA)->orWhere('type', Contact::TYPE_SUPERADMIN)->get();
            foreach ($contacts as $contact) {
                if ($contact->phone) {
                    dispatch(new SendToWhatsappNotificationJob($contact->phone, $messageWhatsapp));
                }
            }
        } catch (\Throwable $th) {
            Log::error("Failed to send balance adjustment notification: " . $th->getMessage());
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SaldoHistory $saldoHistory)
    {
        if (!Auth::user()->can('Edit Saldo Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.saldo-history.create-edit', compact('saldoHistory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SaldoHistoryRequest $request, SaldoHistory $saldoHistory)
    {
        if (!Auth::user()->can('Edit Saldo Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        $saldoHistory->update($data);
        return redirect()->route('saldo-history.index')->with('success', 'Saldo History berhasil diubah');
    }

    public function destroy(string $id)
    {
        if (!Auth::user()->can('Edit Saldo Santri')) {
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
            'message' => 'Arsip riwayat topup saldo berhasil dihapus.'
        ]);
    }

    public function updateStatusPayment(UpdateStatusTopupSaldoRequest $request, $id)
    {
        if (!Auth::user()->can('Edit Saldo Santri') && !Auth::user()->can('Manage Saldo Santri') && !Auth::user()->isKoordinatorCahayaMart()) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $transaction = Transaction::findOrFail($id);
        $data = $request->validated();
        $data['admin_id'] = Auth::id();

        $result = TransactionService::updateStatusPaymentTransfer($data, $transaction);

        if ($result['status']) {
            return $this->postSuccessResponse($result['message'], $result['transaction']);
        } else {
            return $this->failedResponse($result['message']);
        }
    }

    public function import(Request $request)
    {
        if (!Auth::user()->can('Edit Saldo Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            DB::transaction(function () use ($request) {
                Excel::import(new SaldoHistoryImport, $request->file('file'));
            });

            return redirect()->route('saldo-history.index')->with('success', 'Berhasil import data saldo');
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Import Saldo History Failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal mengimpor data saldo. Pastikan sesuai template');
        }
    }

    private function getArchiveTransactionData()
    {
        $transactions = Transaction::with(['student.classroom.school.saldoBank.bank', 'paymentMethod', 'activeProof.bank', 'transactionProofs.bank', 'admin'])
            ->where('type', Transaction::TYPE_SALDO)
            ->whereIn('status', [Transaction::STATUS_PAID, Transaction::STATUS_REJECTED])
            ->where(function ($q) {
                $q->where('is_deleted_from_archive', false)->orWhereNull('is_deleted_from_archive');
            })
            ->hasSchool();

        if ($schoolId = request()->school_id) {
            $transactions->whereHas('student.classroom', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }
        if ($classroomId = request()->classroom_id) {
            $transactions->whereHas('student', function ($q) use ($classroomId) {
                $q->where('classroom_id', $classroomId);
            });
        }

        if ($searchName = request()->search_name) {
            $transactions->whereHas('student', function ($q) use ($searchName) {
                $q->where('name', 'like', "%{$searchName}%")
                  ->orWhere('nis', 'like', "%{$searchName}%")
                  ->orWhere('nisn', 'like', "%{$searchName}%");
            });
        }
        $startDate = request()->filled('start_date') ? request()->start_date : now()->subDays(6)->format('Y-m-d');
        $endDate = request()->filled('end_date') ? request()->end_date : now()->format('Y-m-d');
        if ($startDate) {
            $transactions->whereDate('updated_at', '>=', $startDate);
        }
        if ($endDate) {
            $transactions->whereDate('updated_at', '<=', $endDate);
        }

        $transactions->latest('updated_at');

        return DataTables::of($transactions)
            ->addColumn('proof', function ($transaction) {
                $proof = $transaction->activeProof ?? $transaction->transactionProofs->first();
                $proofUrl = $proof?->proof_image_url ?? $proof?->proof_image;
                if (!$proofUrl) {
                    return '<span class="badge bg-light text-muted fs-8 px-2 py-1"><i class="fas fa-file-invoice text-gray-400 me-1"></i> Tanpa Bukti</span>';
                }
                return "<img src='{$proofUrl}' class='img-fluid img-thumbnail cursor-pointer view-proof-image shadow-sm' data-src='{$proofUrl}' style='max-width: 65px; max-height: 65px; object-fit: cover; border-radius: 12px; border: 1px solid #e2e8f0;' alt='Bukti Transfer' title='Klik untuk melihat bukti full'>";
            })
            ->editColumn('pay_amount', function ($transaction) {
                $badgeClass = $transaction->status == Transaction::STATUS_PAID ? 'bg-light-success text-success' : 'bg-light-danger text-danger';
                return '<span class="badge ' . $badgeClass . ' fw-bolder fs-6 px-3 py-2">Rp ' . number_format($transaction->pay_amount ?? 0, 0, ',', '.') . '</span>';
            })
            ->editColumn('unique_payment', function ($transaction) {
                if ($transaction->unique_payment) {
                    return '<span class="badge bg-light-primary text-primary font-monospace fw-bold px-2 py-1 fs-7">#' . e($transaction->unique_payment) . '</span>';
                }
                return '<span class="text-muted fs-8 font-monospace">-</span>';
            })
            ->editColumn('status', function ($transaction) {
                $statusHtml = '';
                if ($transaction->status == Transaction::STATUS_PAID) {
                    $statusHtml = '<span class="badge badge-success fw-bold px-3 py-2 fs-7">Lunas</span>';
                } elseif ($transaction->status == Transaction::STATUS_REJECTED) {
                    $statusHtml = '<span class="badge badge-danger fw-bold px-3 py-2 fs-7">Ditolak</span>';
                    if ($transaction->activeProof?->note) {
                        $statusHtml .= '<br><small class="text-muted mt-1 d-block">' . e($transaction->activeProof->note) . '</small>';
                    }
                } else {
                    $statusHtml = '<span class="badge badge-light fw-bold px-3 py-2 fs-7">' . e($transaction->status) . '</span>';
                }

                if ($transaction->created_at) {
                    $formattedDate = strtoupper(\Carbon\Carbon::parse($transaction->created_at)->translatedFormat('d-M-Y , H : i'));
                    $statusHtml .= "<br><div class='text-slate-400 mt-1' style='font-size: 11px; font-style: italic; color: #94a3b8;'>{$formattedDate}</div>";
                }

                return $statusHtml;
            })
            ->addColumn('action', function ($transaction) {
                return $this->formatArchiveActionColumn($transaction);
            })
            ->addColumn('bank_recipient', function ($transaction) {
                $proof = $transaction->activeProof ?? $transaction->transactionProofs->first();
                $bank = $proof?->bank;
                if (!$bank) {
                    $banks = $transaction->banks;
                    $bank = $banks->first();
                }
                if (!$bank) return '-';
                return "<div class='d-flex flex-column align-items-start'>
                    <span class='fw-bolder text-gray-800 fs-7'>" . e($bank->name) . "</span>
                    <span class='text-muted fs-8'>No: " . e($bank->account_number) . "</span>
                    <span class='text-muted fs-8'>A.N: " . e($bank->account_name) . "</span>
                </div>";
            })
            ->addColumn('officer', function ($transaction) {
                return $transaction->admin?->name ?? '-';
            })
            ->addColumn('updated_at_formatted', function ($transaction) {
                return $transaction->updated_at ? $transaction->updated_at->translatedFormat('d F Y H:i') : '-';
            })
            ->rawColumns(['proof', 'action', 'status', 'bank_recipient', 'pay_amount', 'unique_payment'])
            ->make(true);
    }

    private function formatArchiveActionColumn($transaction)
    {
        if (!Auth::user()->can('Edit Saldo Santri')) {
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

    public function deleteHistory($id)
    {
        if (!Auth::user()->hasRole('Super Admin')) {
            return response()->json([
                'code' => '403',
                'message' => 'Hanya Super Admin yang dapat menghapus riwayat saldo.'
            ], 403);
        }

        $history = SaldoHistory::findOrFail($id);
        $student = $history->student;

        if (!$student) {
            return response()->json([
                'code' => '404',
                'message' => 'Data siswa tidak ditemukan.'
            ], 404);
        }

        // Adjust student balance back based on transaction type
        if ($history->type === SaldoHistory::TYPE_IN) {
            if ($student->saldo < $history->amount) {
                return response()->json([
                    'code' => '422',
                    'message' => 'Tidak dapat menghapus riwayat Top-Up: Saldo santri saat ini (Rp ' . number_format($student->saldo, 0, ',', '.') . ') tidak mencukupi untuk dikurangi kembali sebesar Rp ' . number_format($history->amount, 0, ',', '.') . '.'
                ], 422);
            }
            Student::where('id', $student->id)
                ->where('saldo', '>=', $history->amount)
                ->decrement('saldo', $history->amount);
        } elseif ($history->type === SaldoHistory::TYPE_OUT || $history->type === SaldoHistory::TYPE_WITHDRAW) {
            Student::where('id', $student->id)->increment('saldo', $history->amount);
        }

        // Delete the associated TransactionDetail if exists
        $transactionDetail = TransactionDetail::where('saldo_history_id', $history->id)->first();
        if ($transactionDetail) {
            $transactionDetail->delete();
        }

        // Delete the history record
        $history->delete();

        return response()->json([
            'code' => '200',
            'message' => 'Riwayat saldo berhasil dihapus dan saldo siswa telah disesuaikan.'
        ]);
    }

    public function batchResetZero(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['code' => 401, 'message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $hasAccess = false;
        try {
            if ($user->can('Create Saldo Santri') || $user->can('Manage Saldo Santri') || (method_exists($user, 'isKoordinatorCahayaMart') && $user->isKoordinatorCahayaMart())) {
                $hasAccess = true;
            }
        } catch (\Throwable $e) {}

        try {
            if (method_exists($user, 'hasRole') && ($user->hasRole('Super Admin') || $user->hasRole('Admin'))) {
                $hasAccess = true;
            }
        } catch (\Throwable $e) {}

        if (!$hasAccess && Auth::guard('web')->check()) {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            return response()->json(['code' => 403, 'message' => 'Maaf, Anda tidak memiliki akses untuk aksi tersebut'], 403);
        }

        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'string'
        ]);

        $studentIds = $request->input('student_ids', []);
        if (empty($studentIds)) {
            return response()->json(['code' => 400, 'message' => 'Pilih minimal satu siswa untuk di-reset saldonya.'], 400);
        }

        @set_time_limit(300);

        try {
            $resetCount = 0;
            $updatedStudents = [];

            DB::transaction(function () use ($studentIds, &$resetCount, &$updatedStudents) {
                $paymentMethod = PaymentMethod::where('type', PaymentMethod::TYPE_CASH)->first();
                if (!$paymentMethod) {
                    $paymentMethod = PaymentMethod::create([
                        'type' => PaymentMethod::TYPE_CASH,
                        'name' => 'Tunai / Cash',
                        'is_active' => true,
                    ]);
                }

                foreach ($studentIds as $id) {
                    $student = Student::where('id', $id)->lockForUpdate()->first();
                    if (!$student) {
                        continue;
                    }

                    $currentSaldo = (int) ($student->saldo ?? 0);
                    if ($currentSaldo === 0) {
                        $updatedStudents[] = [
                            'id' => $student->id,
                            'new_saldo' => 0,
                            'formatted_new_saldo' => 'Rp 0'
                        ];
                        continue;
                    }

                    $balanceBefore = $currentSaldo;
                    $adjustAmount = abs($currentSaldo);
                    $type = ($currentSaldo < 0) ? SaldoHistory::TYPE_IN : SaldoHistory::TYPE_WITHDRAW;

                    // Set student balance directly to 0
                    $student->saldo = 0;
                    $student->save();

                    // Create transaction for audit tracking
                    $reqObj = new Request([
                        'student_id' => $student->id,
                        'amount' => $adjustAmount,
                        'type' => $type,
                        'description' => 'Penyesuaian Reset Saldo ke Rp. 0 oleh ' . Auth::user()->name
                    ]);

                    $transaction = TransactionService::createTransaction($reqObj, $paymentMethod->type, Transaction::TYPE_SALDO);

                    $saldoHistory = SaldoHistory::create([
                        'student_id' => $student->id,
                        'amount' => $adjustAmount,
                        'type' => $type,
                        'description' => 'Penyesuaian Reset Saldo ke Rp. 0 oleh ' . Auth::user()->name,
                        'status' => SaldoHistory::STATUS_SUCCESS,
                        'admin_id' => Auth::id(),
                        'balance_before' => $balanceBefore,
                        'balance_after' => 0,
                    ]);

                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'saldo_history_id' => $saldoHistory->id,
                    ]);

                    $resetCount++;
                    $updatedStudents[] = [
                        'id' => $student->id,
                        'new_saldo' => 0,
                        'formatted_new_saldo' => 'Rp 0'
                    ];
                }
            });

            return response()->json([
                'code' => 200,
                'message' => "Berhasil me-reset saldo {$resetCount} santri menjadi Rp 0.",
                'reset_count' => $resetCount,
                'updated_students' => $updatedStudents
            ]);
        } catch (\Throwable $e) {
            Log::error("Batch Reset Zero Saldo Failed: " . $e->getMessage());
            return response()->json([
                'code' => 500,
                'message' => 'Gagal me-reset saldo santri: ' . $e->getMessage()
            ], 500);
        }
    }

    public function recalculate(Request $request)
    {
        try {
            $studentId = $request->student_id;
            if ($studentId) {
                \App\Services\SaldoRecalculatorService::recalculateForStudent($studentId);
                $message = "Berhasil me-rekalkulasi saldo untuk siswa yang dipilih.";
            } else {
                $count = \App\Services\SaldoRecalculatorService::recalculateAllStudents();
                $message = "Berhasil me-rekalkulasi dan memperbaiki urutan running balance untuk {$count} siswa.";
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Throwable $e) {
            Log::error("Manual Saldo Recalculate Failed: " . $e->getMessage());
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'Gagal me-rekalkulasi saldo: ' . $e->getMessage()
            ], 500);
        }
    }
}

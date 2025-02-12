<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Contact;
use App\Models\Student;
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
        if (!Auth::user()->can('Manage Saldo Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax() && request()->type === 'saldo') {
            $data = SaldoHistory::with('student')->hasSchool()->latest();
            return DataTables::of($data)
                ->addColumn('date', function ($data) {
                    return $data->created_at->translatedFormat('d F Y' . ' <br>' . 'H:i:s');
                })
                ->editColumn('amount', function ($data) {
                    // Format amount menjadi rupiah dengan pemisah ribuan
                    $formattedAmount = 'Rp ' . number_format($data->amount, 0, ',', '.');

                    if ($data->type === 'IN') {
                        return '<span class="badge bg-success">+' . $formattedAmount . '</span>';
                    } else {
                        return '<span class="badge bg-danger">-' . $formattedAmount . '</span>';
                    }
                })
                ->editColumn('balance_before', function ($data) {
                    return '<span class="badge bg-info">Rp ' . number_format($data->balance_before, 0, ',', '.') . '</span>';
                })
                ->editColumn('balance_after', function ($data) {
                    return '<span class="badge bg-info">Rp ' . number_format($data->balance_after, 0, ',', '.') . '</span>';
                })
                ->editColumn('status', function ($data) {
                    if ($data->status === SaldoHistory::STATUS_SUCCESS) {
                        return '<span class="badge bg-success">' . $data->status . '</span>';
                    } elseif ($data->status === SaldoHistory::STATUS_PENDING) {
                        return '<span class="badge bg-warning">' . $data->status . '</span>';
                    } else {
                        return '<span class="badge bg-danger">' . $data->status . '</span>';
                    }
                })
                ->rawColumns(['amount', 'status', 'date', 'balance_before', 'balance_after'])
                ->make(true);
        }
        if (request()->ajax() && request()->type === 'topup') {
            $transactions = Transaction::with('student', 'paymentMethod', 'activeProof')
                ->whereHas('paymentMethod', function ($query) {
                    $query->where('type', PaymentMethod::TYPE_TRANSFER);
                })
                ->where('type', Transaction::TYPE_SALDO)
                ->where('status', Transaction::STATUS_PENDING_CONFIRMATION)
                ->hasSchool()
                ->latest();

            return DataTables::of($transactions)
                ->addColumn('proof', function ($transaction) {
                    // add image preview on click zoom the image
                    return "<a href='" . $transaction?->activeProof?->proof_image . "' target='_blank'>
                        <img src='" . $transaction?->activeProof?->proof_image . "' class='img-fluid img-thumbnail' style='max-width: 100px;'>
                    </a>";
                })
                ->editColumn('pay_amount', function ($transaction) {
                    return 'Rp ' . number_format($transaction->pay_amount, 0, ',', '.');
                })

                ->editColumn('status', function ($transaction) {
                    if ($transaction->status == Transaction::STATUS_PENDING) {
                        return '<span class="badge badge-primary">Belum Dibayar</span>';
                    } elseif ($transaction->status == Transaction::STATUS_PENDING_PAYMENT) {
                        return '<span class="badge badge-warning">Menunggu Pembayaran</span>';
                    } elseif ($transaction->status == Transaction::STATUS_PENDING_CONFIRMATION) {
                        return '<span class="badge badge-danger">Menunggu Verifikasi</span>';
                    } elseif ($transaction->status == Transaction::STATUS_PAID) {
                        return '<span class="badge badge-success">Lunas</span>';
                    } elseif ($transaction->status == Transaction::STATUS_EXPIRED) {
                        return '<span class="badge badge-secondary">Kedaluwarsa</span>';
                    } elseif ($transaction->status == Transaction::STATUS_CANCELLED) {
                        return '<span class="badge badge-secondary">Dibatalkan</span>';
                    } elseif ($transaction->status == Transaction::STATUS_REJECTED) {
                        return '<span class="badge badge-danger">Ditolak</span><br><small>' . $transaction->activeProof?->note . '</small>';
                    }
                })
                ->addColumn('action', function ($transaction) {
                    if (Auth::user()->can('Edit Saldo Santri')) {
                        $action = "<select class='form-control status-transaction' name='status' id='status-{$transaction->id}' onchange='updateStatus(this.value, \"{$transaction->id}\")'>
                        <option value=''>Pilih Status</option>
                        <option value='" . Transaction::STATUS_PAID . "' " . ($transaction->status == Transaction::STATUS_PAID ? 'selected' : '') . ">Lunas</option>
                        <option value='" . Transaction::STATUS_REJECTED . "' " . ($transaction->status == Transaction::STATUS_REJECTED ? 'selected' : '') . ">Cek Ulang</option>
                    </select>";


                        $action .= "<input type='hidden' name='note' id='note-{$transaction->id}' value='{$transaction->activeProof?->note}'>";

                        // Tambahkan button simpan
                        $action .= "<button class='btn btn-primary btn-sm mt-2' onclick='saveStatus(\"{$transaction->id}\")'>Simpan</button>";

                        // Jika status sudah lunas maka tidak bisa diubah
                        if ($transaction->status == Transaction::STATUS_PAID) {
                            $action = "<span class='badge badge-success'>Lunas</span>";
                        }

                        return $action;
                    }
                })


                ->rawColumns(['proof', 'action', 'type', 'status'])
                ->make(true);
        }
        return view('admins.saldo-history.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Saldo Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.saldo-history.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(SaldoHistoryRequest $request)
    {
        if (!Auth::user()->can('Create Saldo Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Clean the request amount by removing thousand separators and commas
        $amount = preg_replace('/[.,]/', '', $request->amount);
        $request->merge(['amount' => $amount]);

        $studentId = $request->student_id;
        $cacheKey = "student_transaction_{$studentId}";

        // Check if there's an active transaction in the cache
        if (Cache::has($cacheKey)) {
            return redirect()->route('saldo-history.index')->with('error', 'Transaksi sedang diproses, silakan coba lagi nanti');
        }

        // Set a cache entry to lock the transaction
        Cache::put($cacheKey, true, now()->addMinutes(5)); // Lock for 5 minutes

        DB::beginTransaction();

        try {
            $paymentMethod = PaymentMethod::where('type', PaymentMethod::TYPE_CASH)->first();
            $paymentMethodType = $paymentMethod->type;

            $transaction = TransactionService::createTransaction($request, $paymentMethodType, Transaction::TYPE_SALDO);

            if ($request->type === SaldoHistory::TYPE_WITHDRAW) {
                // Check if student has enough saldo
                $student = Student::findOrFail($studentId);
                if ($student->saldo < $request->amount) {
                    Cache::forget($cacheKey); // Clear cache if transaction cannot proceed
                    return redirect()->route('saldo-history.index')->with('error', 'Saldo Santri tidak mencukupi');
                }

                // Reduce saldo
                $transaction->student->saldo -= $request->amount;
                $transaction->student->save();

                // Make saldo history
                $saldoHistory = SaldoHistory::create([
                    'student_id' => $transaction->student_id,
                    'amount' => $request->amount,
                    'type' => SaldoHistory::TYPE_WITHDRAW,
                    'description' => $request->description ?? 'Penarikan Saldo Rp.' . number_format($request->amount, 0, ',', '.') . ' oleh ' . Auth::user()->name,
                    'status' => SaldoHistory::STATUS_SUCCESS,
                    'admin_id' => Auth::id()
                ]);

                $transactionDetail = TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'saldo_history_id' => $saldoHistory->id
                ]);
            } else {
                // Add saldo
                $transaction->student->saldo += $request->amount;
                $transaction->student->save();

                // Make saldo history
                $saldoHistory = SaldoHistory::create([
                    'student_id' => $transaction->student_id,
                    'amount' => $request->amount,
                    'type' => SaldoHistory::TYPE_IN,
                    'description' => $request->description ?? 'Topup Saldo Rp.' . number_format($request->amount, 0, ',', '.') . ' oleh ' . Auth::user()->name,
                    'status' => SaldoHistory::STATUS_SUCCESS,
                    'admin_id' => Auth::id()
                ]);

                $transactionDetail = TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'saldo_history_id' => $saldoHistory->id
                ]);
            }

            DB::commit();

            // Clear the cache entry
            Cache::forget($cacheKey);

            $this->sendNotifications($transaction->student, $transaction->transactionDetails()?->first()?->saldoHistory);

            return redirect()->route('saldo-history.index')->with('success', 'Berhasil Topup Saldo');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            // Ensure the cache entry is cleared in case of an error
            Cache::forget($cacheKey);

            return redirect()->route('saldo-history.index')->with('error', 'Gagal Topup Saldo');
        }
    }

    private function sendNotifications($student, $saldoHistory)
    {
        $activity = ($saldoHistory->type === 'IN') ? 'Topup Saldo' : 'Tarik Saldo';
        $title = 'Pemberitahuan Saldo';
        $body = 'Santri ' . $student->name . ' telah melakukan ' . $activity . ' sebesar Rp. ' . number_format($saldoHistory->amount, 0, ',', '.') .
            ', Saldo Terkini Rp. ' . number_format($student->saldo, 0, ',', '.');
        $messageWhatsapp = SendNotifWaService::balanceAdjustment($student, $saldoHistory, "SALDO");

        dispatch(new SendToPushNotificationJob($title, $body, $student->user, null));
        dispatch(new SendToWhatsappNotificationJob($student->user?->phone, $messageWhatsapp));

        $contacts = Contact::where('type', Contact::TYPE_BENDAHARA)->orWhere('type', Contact::TYPE_SUPERADMIN)->get();
        foreach ($contacts as $contact) {
            dispatch(new SendToWhatsappNotificationJob($contact->phone, $messageWhatsapp));
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function updateStatusPayment(UpdateStatusTopupSaldoRequest $request, $id)
    {
        if (!Auth::user()->can('Edit Saldo Santri')) {
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
}

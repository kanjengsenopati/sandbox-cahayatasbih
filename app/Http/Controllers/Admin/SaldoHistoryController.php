<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\SaldoHistory;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\SaldoHistoryRequest;
use App\Http\Requests\Admin\UpdateStatusTopupSaldoRequest;

class SaldoHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax() && request()->type === 'saldo') {
            $data = SaldoHistory::with('student')->latest()->get();
            return DataTables::of($data)
                ->editColumn('amount', function ($data) {
                    if ($data->type === 'IN') {
                        return '<span class="badge bg-success">+' . $data->amount . '</span>';
                    } else {
                        return '<span class="badge bg-danger">-' . $data->amount . '</span>';
                    }
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
                ->rawColumns(['amount', 'status'])
                ->make(true);
        }
        if (request()->ajax() && request()->type === 'topup') {
            $transactions = Transaction::with('student', 'paymentMethod', 'activeProof')
                ->whereHas('paymentMethod', function ($query) {
                    $query->where('type', PaymentMethod::TYPE_TRANSFER);
                })
                ->where('type', Transaction::TYPE_SALDO)
                ->where('status', Transaction::STATUS_PENDING_CONFIRMATION)
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
                        return '<span class="badge badge-danger">Menunggu Konfirmasi</span>';
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
                    $action = "<select class='form-control status-transaction' name='status' id='status-{$transaction->id}' onchange='updateStatus(this.value, \"{$transaction->id}\")'>
                        <option value=''>Pilih Status</option>
                        <option value='" . Transaction::STATUS_PENDING_CONFIRMATION . "' " . ($transaction->status == Transaction::STATUS_PENDING_CONFIRMATION ? 'selected' : '') . ">Menunggu Konfirmasi</option>
                        <option value='" . Transaction::STATUS_PAID . "' " . ($transaction->status == Transaction::STATUS_PAID ? 'selected' : '') . ">Lunas</option>
                        <option value='" . Transaction::STATUS_REJECTED . "' " . ($transaction->status == Transaction::STATUS_REJECTED ? 'selected' : '') . ">Ditolak</option>
                    </select>";


                    $action .= "<input type='hidden' name='note' id='note-{$transaction->id}' value='{$transaction->activeProof?->note}'>";

                    // Tambahkan button simpan
                    $action .= "<button class='btn btn-primary btn-sm mt-2' onclick='saveStatus(\"{$transaction->id}\")'>Simpan</button>";

                    // Jika status sudah lunas maka tidak bisa diubah
                    if ($transaction->status == Transaction::STATUS_PAID) {
                        $action = "<span class='badge badge-success'>Lunas</span>";
                    }

                    return $action;
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
        return view('admins.saldo-history.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(SaldoHistoryRequest $request)
    {
        // Start a database transaction
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Retrieve the student
            $student = Student::findOrFail($data['student_id']);

            // Check if saldo deduction exceeds user's saldo
            if ($data['type'] === 'OUT' && $student->saldo < $data['amount']) {
                return redirect()->route('saldo-history.index')->with('error', 'Saldo tidak mencukupi');
            }
            $data['usage'] = SaldoHistory::USAGE_TOPUP;

            // Update saldo and create description
            if ($data['type'] === 'IN') {
                $student->saldo += $data['amount'];
                $description = 'Saldo ditambahkan sebesar ' . $data['amount'] . ' oleh ' . auth()->user()->name;
            } else {
                $student->saldo -= $data['amount'];
                $description = 'Saldo dikurangi sebesar ' . $data['amount'] . ' oleh ' . auth()->user()->name;
            }
            $student->save();

            // Create saldo history
            $data['description'] = $description;
            $data['status'] = SaldoHistory::STATUS_SUCCESS;
            SaldoHistory::create($data);

            // Commit the transaction
            DB::commit();

            return redirect()->route('saldo-history.index')->with('success', 'Penyesuaian saldo berhasil');
        } catch (\Exception $e) {
            // If an exception occurs, rollback the transaction
            Log::error($e->getMessage());
            DB::rollBack();

            // You can handle the exception here (e.g., log it, display an error message, etc.)
            return redirect()->route('saldo-history.index')->with('error', $e->getMessage());
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
        return view('admins.saldo-history.create-edit', compact('saldoHistory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SaldoHistoryRequest $request, SaldoHistory $saldoHistory)
    {
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
}

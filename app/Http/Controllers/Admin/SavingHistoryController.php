<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\SaldoHistory;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\SavingHistory;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\SavingHistoryRequest;
use App\Http\Requests\Admin\UpdateStatusTopupSavingRequest;

class SavingHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Tabungan Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax() && request()->type === 'saving') {
            $data = SavingHistory::with('student')->hasSchool()->latest();
            return DataTables::of($data)
                ->editColumn('date', function ($data) {
                    Carbon::setLocale('id'); // Set locale to Indonesian
                    return Carbon::parse($data->created_at)
                        ->translatedFormat('d F Y'); // Format tanggal dalam bahasa Indonesia
                })
                ->editColumn('amount', function ($data) {
                    if ($data->type === 'IN') {
                        return '<span class="badge bg-success">+' . number_format($data->amount, 0, ',', '.') . '</span>';
                    } else {
                        return '<span class="badge bg-danger">-' . number_format($data->amount, 0, ',', '.') . '</span>';
                    }
                })
                ->editColumn('status', function ($data) {
                    if ($data->status === SavingHistory::STATUS_SUCCESS) {
                        return '<span class="badge bg-success">' . $data->status . '</span>';
                    } elseif ($data->status === SavingHistory::STATUS_PENDING) {
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
                ->where('type', Transaction::TYPE_SAVING)
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
                    if (Auth::user()->can('Edit Tabungan Santri')) {
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
        return view('admins.saving-history.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Tabungan Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.saving-history.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SavingHistoryRequest $request)
    {
        if (!Auth::user()->can('Create Tabungan Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        DB::beginTransaction();

        try {
            $data = $request->validated();

            // Retrieve the student
            $student = Student::findOrFail($data['student_id']);

            // Check if saldo deduction exceeds user's saldo
            if ($data['type'] === 'OUT' && $student->saving < $data['amount']) {
                return redirect()->route('saving-history.index')->with('error', 'Tabungan tidak mencukupi');
            }

            // Update saldo and create description
            if ($data['type'] === 'IN') {
                $student->saving += $data['amount'];
                $description = 'Tabungan ditambahkan sebesar ' . $data['amount'] . ' oleh ' . auth()->user()->name;
            } else {
                $student->saving -= $data['amount'];
                $description = 'Tabungan dikurangi sebesar ' . $data['amount'] . ' oleh ' . auth()->user()->name;
            }
            $student->save();

            // Create saldo history
            $data['description'] = $description;
            $data['status'] = SavingHistory::STATUS_SUCCESS;
            $data['admin_id'] = auth()->id();
            SavingHistory::create($data);

            // Commit the transaction
            DB::commit();

            return redirect()->route('saving-history.index')->with('success', 'Tabungan berhasil ditambahkan');
        } catch (\Exception $e) {
            // If an exception occurs, rollback the transaction
            Log::error($e->getMessage());
            DB::rollBack();

            return redirect()->route('saving-history.index')->with('error', 'Tabungan gagal ditambahkan');
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function updateStatusPayment(UpdateStatusTopupSavingRequest $request, $id)
    {
        if (!Auth::user()->can('Edit Tabungan Santri')) {
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
}

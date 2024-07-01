<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bill;
use App\Models\User;
use App\Models\School;
use App\Models\Contact;
use App\Models\Student;
use App\Models\BillType;
use App\Models\Transaction;
use App\Models\SaldoHistory;

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
use App\Jobs\SendToPushNotificationJob;
use App\Jobs\SendToWhatsappNotificationJob;
use App\Http\Requests\Admin\BillPaymentRequest;
use App\Http\Requests\Admin\UpdateTransactionStatusRequest;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schools = School::orderBy('name', 'asc')->get();
        if (request()->student_id) {

            $id = request()->student_id;
            $student = Student::findOrFail($id);

            // Mengambil tagihan bulanan
            $billMonth = BillType::with('billItem', 'academicYear', 'bills')
                ->where('type', BillType::TYPE_MONTHLY)
                ->whereHas('bills', function ($query) use ($id) {
                    $query->where('student_id', $id);
                })
                ->latest()
                ->get()
                ->map(function ($item) use ($id) {
                    $item->total_unpaid = Bill::where('student_id', $id)
                        ->where('bill_type_id', $item->id)
                        ->where('status', Bill::STATUS_UNPAID)
                        ->sum('amount');

                    $item->total_paid = Bill::where('student_id', $id)
                        ->where('bill_type_id', $item->id)
                        ->where('status', Bill::STATUS_PAID)
                        ->sum('amount');

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
                        ->where('status', Bill::STATUS_UNPAID)
                        ->sum('amount');
                    $item->total_paid = Bill::where('student_id', $id)
                        ->where('bill_type_id', $item->id)
                        ->where('status', Bill::STATUS_PAID)
                        ->sum('amount');

                    return $item;
                });


            return view('admins.bill.index', compact('student', 'billMonth', 'billOthers', 'schools'));
        }

        if (request()->ajax()) {
            $transactions = Transaction::with('student', 'paymentMethod', 'activeProof')
                ->whereHas('paymentMethod', function ($query) {
                    $query->where('type', PaymentMethod::TYPE_TRANSFER);
                })
                ->where('type', Transaction::TYPE_BILL)
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
        return view('admins.bill.index', compact('schools'));
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
        DB::beginTransaction();

        try {
            $paymentMethodType = $request->payment_method;

            $transaction = TransactionService::createTransaction($request, $paymentMethodType, Transaction::TYPE_BILL);
            if ($transaction->status == Transaction::STATUS_PAID) {
                TransactionService::dispatchNotifications($transaction);
            }
            DB::commit();
            return redirect()->back()->with('success', "Transaksi pembayaran berhasil");
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return redirect()->back()->with('error', "Transaksi pembayaran gagal");
        }
    }

    /**
     * Display the specified resource.
     */

    public function show(string $id)
    {
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

        $result = TransactionService::updateStatusPaymentTransfer($data, $transaction);

        if ($result['status']) {
            return $this->postSuccessResponse($result['message'], $result['transaction']);
        } else {
            return $this->failedResponse($result['message']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getBillData()
    {

        $id = request()->student_id;
        $student = Student::findOrFail($id);

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
                    ->where('status', Bill::STATUS_UNPAID)
                    ->sum('amount');

                $item->total_paid = Bill::where('student_id', $id)
                    ->where('bill_type_id', $item->id)
                    ->where('status', Bill::STATUS_PAID)
                    ->sum('amount');

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
                    ->where('status', Bill::STATUS_UNPAID)
                    ->sum('amount');
                $item->total_paid = Bill::where('student_id', $id)
                    ->where('bill_type_id', $item->id)
                    ->where('status', Bill::STATUS_PAID)
                    ->sum('amount');

                return $item;
            });


        return view('admins.bill.show', compact('student', 'billMonth', 'billOthers'));
    }

    public function summaryBill()
    {
        $requestData = request()->only(['student_id', 'bill_type_id']);

        $student = Student::findOrFail($requestData['student_id']);
        $billType = BillType::findOrFail($requestData['bill_type_id']);

        $billType->load(['bills' => function ($query) use ($student, $billType) {
            $query->where('student_id', $student->id)->where('bill_type_id', $billType->id);
        }]);

        $totalBill = $billType->bills->sum('amount');
        $totalPaid = $billType->bills->where('status', Bill::STATUS_PAID)->sum('amount');
        $totalUnpaid = $billType->bills->where('status', Bill::STATUS_UNPAID)->sum('amount');

        $billType->total_bill = $totalBill ?? 0;
        $billType->total_paid = $totalPaid ?? 0;
        $billType->total_unpaid = $totalUnpaid ?? 0;

        $bills = Bill::with('transactions')->where('student_id', $requestData['student_id'])
            ->where('bill_type_id', $requestData['bill_type_id'])
            ->orderBy('month', 'asc')
            ->get();


        $paymentMethods = PaymentMethod::latest()->get();

        return view('admins.bill.summary', compact('student', 'billType', 'bills', 'paymentMethods'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bill;
use App\Models\User;
use App\Models\School;
use App\Models\Student;
use App\Models\BillType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;

use App\Models\TransactionProof;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\SendNotifWaService;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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
                ->where('status', Transaction::STATUS_PENDING_PAYMENT)
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
                ->editColumn('type', function ($transaction) {
                    if ($transaction->type == Transaction::TYPE_BILL) {
                        return '<span class="badge badge-primary">Tagihan</span>';
                    } elseif ($transaction->type == Transaction::TYPE_SALDO) {
                        return '<span class="badge badge-success">Saldo</span>';
                    } elseif ($transaction->type == Transaction::TYPE_SAVING) {
                        return '<span class="badge badge-info">Tabungan</span>';
                    } elseif ($transaction->type == Transaction::TYPE_PPDB) {
                        return '<span class="badge badge-warning">PPDB</span>';
                    }
                })
                ->addColumn('action', function ($transaction) {
                    $actionShow = route('bill.show', $transaction->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.show', ['action' => $actionShow]) .
                        "</div>";
                })
                ->rawColumns(['proof', 'action', 'type'])
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

            $transaction = TransactionService::createTransaction($request, $paymentMethodType);
            if ($paymentMethodType == PaymentMethod::TYPE_XENDIT) {
                TransactionService::createInvoice($transaction);
            } elseif ($paymentMethodType == PaymentMethod::TYPE_BALANCE) {
                $payAmount = $transaction->pay_amount;
                $student = Student::find($request->student_id);

                if ($student->saldo < $payAmount) {
                    DB::rollBack();
                    return redirect()->back()->with('error', "Maaf Saldo Santri tidak mencukupi");
                }
                $transaction->update([
                    'payment_method_id' => PaymentMethod::where('type', $paymentMethodType)
                        ->first()->id,
                    'admin_id' => Auth::id(),
                ]);
                TransactionService::payWithBalance($student, $payAmount, $transaction, $request);
            } elseif ($paymentMethodType == PaymentMethod::TYPE_CASH) {
                $transaction->update(['payment_method_id' => PaymentMethod::where('type', $paymentMethodType)
                    ->first()->id]);
                TransactionService::payWithCash($transaction);
            }

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
        DB::beginTransaction();
        try {
            $transaction = Transaction::findOrFail($id);
            $transaction->update($request->validated());
            if ($transaction->status == Transaction::STATUS_PAID) {
                $transaction->activeProof->update([
                    'status' => TransactionProof::STATUS_CONFIRMED
                ]);
                TransactionService::dispatchNotifications($transaction);
            }
            if ($transaction->status == Transaction::STATUS_REJECTED) {
                $transaction->activeProof->update([
                    'status' => TransactionProof::STATUS_REJECTED,
                    'note' => $request->note,
                ]);
            }
            DB::commit();
            return redirect()->back()->with('success', "Status transaksi berhasil diubah");
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th);
            return redirect()->back()->with('error', "Status transaksi gagal diubah");
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

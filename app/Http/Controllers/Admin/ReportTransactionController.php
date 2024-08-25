<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\School;

class ReportTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // if (!Auth::user()->can('Manage Laporan Saldo Santri')) {
        //     return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        // }
        if (request()->ajax()) {
            $data = Transaction::where('status', Transaction::STATUS_PAID)
                ->with('student', 'student.classroom')
                ->when(request()->filled('start_date'), function ($query) {
                    $query->whereDate('created_at', '>=', request()->start_date);
                })
                ->when(request()->filled('end_date'), function ($query) {
                    $query->whereDate('created_at', '<=', request()->end_date);
                })
                ->schoolFilter('school_id', request()->school_id)
                ->classroomFilter('classroom_id', request()->classroom_id)
                ->filter('type', request()->type)
                ->hasSchool()
                ->latest();
            if (request()->data == 'total') {
                $total_bill = $data->where('type', Transaction::TYPE_BILL)->sum('pay_amount');
                $total_saldo = $data->where('type', Transaction::TYPE_SALDO)->sum('pay_amount');
                $saldo_saving = $data->where('type', Transaction::TYPE_SAVING)->sum('pay_amount');

                return response()->json([
                    'total_bill' => number_format($total_bill, 0, ',', '.'),
                    'total_saldo' => number_format($total_saldo, 0, ',', '.'),
                    'saldo_saving' => number_format($saldo_saving, 0, ',', '.'),
                ]);
            } elseif (request()->data == 'table') {
                return DataTables::of($data)
                    ->addColumn('student', function ($data) {
                        return $data->student->name . ' (' . $data->student->classroom->name . ')';
                    })
                    ->addColumn('amount', function ($data) {
                        return number_format($data->pay_amount, 0, ',', '.');
                    })
                    ->addColumn('date', function ($data) {
                        // Mengatur lokal bahasa Indonesia
                        Carbon::setLocale('id');

                        // Pastikan $data->created_at di-cast menjadi Carbon
                        $createdAt = Carbon::parse($data->created_at);

                        // Menggunakan translatedFormat untuk format tanggal dalam bahasa Indonesia
                        return $createdAt->translatedFormat('d F Y' . ' <br>' . 'H:i:s');
                    })

                    ->editColumn('type', function ($data) {
                        if ($data->type == Transaction::TYPE_BILL) {
                            return '<span class="badge badge-primary">Tagihan</span>';
                        } elseif ($data->type == Transaction::TYPE_SALDO) {
                            return '<span class="badge badge-success">Saldo</span>';
                        } elseif ($data->type == Transaction::TYPE_SAVING) {
                            return '<span class="badge badge-info">Tabungan</span>';
                        } else {
                            return '-';
                        }
                    })
                    ->addColumn('payment_method', function ($data) {
                        if ($data->paymentMethod?->type == PaymentMethod::TYPE_BALANCE) {
                            return '<span class="badge badge-success">Saldo</span>';
                        } elseif ($data->paymentMethod?->type == PaymentMethod::TYPE_XENDIT) {
                            return '<span class="badge badge-info">Xendit</span>';
                        } elseif ($data->paymentMethod?->type == PaymentMethod::TYPE_CASH) {
                            return '<span class="badge badge-warning">Tunai</span>';
                        } elseif ($data->paymentMethod?->type == PaymentMethod::TYPE_TRANSFER) {
                            return '<span class="badge badge-primary">Transfer</span>';
                        } else {
                            return '-';
                        }
                    })
                    ->addColumn('staff', function ($data) {
                        return $data->admin?->name ?? '-';
                    })
                    ->rawColumns(['date', 'type', 'payment_method'])
                    ->make(true);
            }
        }
        $schools = School::orderBy('name')->get();
        return view('admins.report-transaction.index', compact('schools'));
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
    public function store(Request $request)
    {
        //
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
}

<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Admin;
use App\Models\School;
use App\Models\BillType;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Yajra\DataTables\DataTables;
use App\Models\TransactionDetail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportTransactionExport;

class ReportTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Transaksi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = Transaction::where('status', Transaction::STATUS_PAID)
                ->with('student', 'student.classroom', 'paymentMethod', 'admin')
                ->when(request()->filled('start_date'), function ($query) {
                    $query->whereDate('created_at', '>=', request()->start_date);
                })
                ->when(request()->filled('end_date'), function ($query) {
                    $query->whereDate('created_at', '<=', request()->end_date);
                })
                ->when(request()->filled('admin_id'), function ($query) {
                    $query->where('admin_id', request()->admin_id);
                })
                ->schoolFilter('school_id', request()->school_id)
                ->classroomFilter('classroom_id', request()->classroom_id)
                ->filter('type', request()->type)
                ->when(request()->filled('bill_type_id'), function ($query) {
                    $query->whereHas('transactionDetails.bill', function ($query) {
                        $query->where('bill_type_id', request()->bill_type_id);
                    });
                })
                ->hasSchool()
                ->latest();
            if (request()->data == 'total') {
                // Fetch all types of transactions and sum them separately
                $totals = $data->selectRaw("
                SUM(CASE WHEN type = '" . Transaction::TYPE_BILL . "' THEN pay_amount ELSE 0 END) as total_bill,
                SUM(CASE WHEN type = '" . Transaction::TYPE_SALDO . "' THEN pay_amount ELSE 0 END) as total_saldo,
                SUM(CASE WHEN type = '" . Transaction::TYPE_SAVING . "' THEN pay_amount ELSE 0 END) as saldo_saving
            ")
                    ->first();

                return response()->json([
                    'total_bill' => number_format($totals->total_bill, 0, ',', '.'),
                    'total_saldo' => number_format($totals->total_saldo, 0, ',', '.'),
                    'saldo_saving' => number_format($totals->saldo_saving, 0, ',', '.'),
                ]);
            } elseif (request()->data == 'table') {
                return DataTables::of($data)
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
                    ->addColumn('item', function ($data) {
                        $transaction_details = TransactionDetail::where('transaction_id', $data->id)->get();
                        if ($data->type == Transaction::TYPE_BILL) {
                            $item = '';
                            foreach ($transaction_details as $index => $detail) {
                                $billType = $detail->bill?->billType?->name ?? '-';
                                $month = Carbon::createFromFormat('m', $detail->bill?->month)->translatedFormat('F');
                                $year = $detail->bill?->year ?? '-';
                                $item .= ($index + 1) . '. ' . $billType . ' ' . $month . ' ' . $year . '<br>';
                            }
                            return $item ?: '-';
                        } elseif ($data->type == Transaction::TYPE_SALDO) {
                            $item = '';
                            foreach ($transaction_details as $index => $detail) {
                                $item .= ($index + 1) . '. ' . ($detail->saldoHistory?->description ?? '-') . '<br>';
                            }
                            return $item ?: '-';
                        } elseif ($data->type == Transaction::TYPE_SAVING) {
                            $item = '';
                            foreach ($transaction_details as $index => $detail) {
                                $item .= ($index + 1) . '. ' . ($detail->savingHistory?->description ?? '-') . '<br>';
                            }
                            return $item ?: '-';
                        } else {
                            return '-';
                        }
                    })
                    ->addColumn('admin', function ($data) {
                        return $data->admin?->name ?? 'N/A';
                    })
                    ->addColumn('action', function ($data) {
                        $actionDelete = route('report-transaction.destroy', $data->id);
                        return "<div class='d-flex gap-2 flex-nowrap justify-content-center'>" .
                            // add icon print invoice
                            "<a href='" . route('transaction.invoice', $data->id) . "' target='_blank' class='btn btn-sm btn-primary' title='Cetak Invoice'><i class='fas fa-print'></i></a>" .
                            // add icon delete
                            view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Laporan Transaksi']) .
                            "</div>";
                        // add delete action

                    })
                    ->rawColumns(['date', 'type', 'payment_method', 'item', 'action'])
                    ->make(true);
            }
        }
        // ambil list admin dari transaction 
        $admin_ids = Transaction::where('status', Transaction::STATUS_PAID)->pluck('admin_id')->unique();
        // ambil list admin nama dari admin_ids
        $admins = Admin::whereIn('id', $admin_ids)->select('id', 'name')->orderBy('name')->get();
        $schools = School::orderBy('name')->get();
        $billTypes = BillType::select('id', 'name')->whereNotIn('id', [
            '02dae620-fc2c-4bf2-9e13-c5c1950e4d48',
            '615a34af-be2d-45f2-9830-720fea341a0c',
            'f3a25c77-f8c0-4882-8286-571bc57bf87c',
            'ce389861-40ab-4523-9364-3458e9dfda1d'
        ])->get();
        return view('admins.report-transaction.index', compact('schools', 'admins', 'billTypes'));
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
        if (!Auth::user()->can('Delete Laporan Transaksi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $transaction = Transaction::with('transactionDetails')->findOrFail($id);
        // delete transaction details
        $transaction->transactionDetails()->delete();
        $transaction->delete();
        return redirect()->back()->with('success', 'Berhasil menghapus transaksi');
    }

    public function export()
    {
        return Excel::download(new ReportTransactionExport, 'Laporan Transaksi ' . request()->start_date . ' - ' . request()->end_date . '.' . request()->type);
    }
}

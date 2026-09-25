<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\ImportLog;
use Yajra\DataTables\DataTables;
use App\Services\TransactionService;

class AuditTagihanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if ($request->tab === 'archive') {
                return $this->getArchiveTransactionData();
            }
        }
        return view('admins.audit.tagihan');
    }

    private function getArchiveTransactionData()
    {
        // Copy the logic from BillController@getArchiveTransactionData
        $query = Transaction::with(['student.classroom.school', 'paymentMethod'])
            ->where('status', Transaction::STATUS_ARCHIVED)
            ->whereNotNull('payment_proof_id')
            ->latest();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('student', function ($row) {
                return $row->student->name . '<br><small class="text-muted">' . ($row->student->nis ?? '-') . '</small>';
            })
            ->editColumn('amount', function ($row) {
                return 'Rp ' . number_format($row->amount, 0, ',', '.');
            })
            ->editColumn('payment_method', function ($row) {
                return $row->paymentMethod->name ?? '-';
            })
            ->addColumn('proof', function ($row) {
                if ($row->paymentProof && $row->paymentProof->file_url) {
                    return '<img src="' . $row->paymentProof->file_url . '" width="50" class="img-thumbnail view-proof-image" data-src="' . $row->paymentProof->file_url . '" style="cursor: pointer;">';
                }
                return '<span class="text-muted">Tidak Ada</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('d F Y H:i');
            })
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-sm btn-danger btn-delete-archive" data-id="' . $row->id . '"><i class="fas fa-trash"></i> Hapus</button>';
            })
            ->rawColumns(['student', 'proof', 'action'])
            ->make(true);
    }
}

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
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('Super Admin') || $user->hasRole('SUPER ADMIN');
        $hasAccess = $isSuperAdmin;

        // Sinkronisasi RBAC Dinamis dari tabel Pengaturan Module (SubMenuNavigation)
        if (!$hasAccess) {
            $menu = \App\Models\SubMenuNavigation::where('url', 'like', '%audit/tagihan-pembayaran%')->first();
            if ($menu && $menu->permission) {
                $permissions = explode(',', $menu->permission);
                foreach ($permissions as $perm) {
                    if ($user->can(trim($perm))) {
                        $hasAccess = true;
                        break;
                    }
                }
            }
        }

        // Blokir jika tidak berhak
        if (!$hasAccess) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses modul ini.');
        }

        if ($request->ajax()) {
            if ($request->tab === 'archive') {
                return $this->getArchiveTransactionData();
            }
        }

        // Fix Error 500: Mengambil data Schools & AcademicYears untuk dropdown import
        $schools = \App\Models\School::orderBy('name')->hasSchool()->get();
        $academicYears = \App\Models\AcademicYear::where(function($query) {
            $query->where('is_active', true)
                  ->orWhereHas('billTypes', function ($q) {
                      $q->where('is_visible', true);
                  });
        })->orderBy('start_year', 'desc')->get();

        return view('admins.audit.tagihan', compact('schools', 'academicYears'));
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

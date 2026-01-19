<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\School;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PointOfSaleTransaction;

class PosTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Tagihan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $data = PointOfSaleTransaction::when(
                request()->filled('start_date') && request()->filled('end_date'),
                function ($query) {
                    $query->whereDate('created_at', '>=', request()->start_date)
                        ->whereDate('created_at', '<=', request()->end_date);
                }
            )
                // ->schoolFilter('school_id', request()->school_id)
                // ->classroomFilter('classroom_id', request()->classroom_id)
                // Apply search on student name if provided
                ->when(request()->has('search') && is_array(request()->search) && isset(request()->search['value']), function ($query) {
                    $searchTerm = request()->search['value'];
                    $query->whereHas('student', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', '%' . $searchTerm . '%');
                    });
                })
                ->latest();

            if (request()->data == 'total') {
                // Fetch all types of transactions and sum them separately
                $totals = $data->selectRaw('SUM(CASE WHEN status = "PAID" THEN pay_amount ELSE 0 END) as total_paid, SUM(CASE WHEN status = "UNPAID" THEN pay_amount ELSE 0 END) as total_unpaid, SUM(pay_amount) as total')->first();

                // Format the totals using number_format
                $formattedTotalPaid = number_format($totals->total_paid, 0, ',', '.');
                $formattedTotalUnpaid = number_format($totals->total_unpaid, 0, ',', '.');
                $formattedTotal = number_format($totals->total, 0, ',', '.');

                return response()->json([
                    'total_paid' => $formattedTotalPaid,
                    'total_unpaid' => $formattedTotalUnpaid,
                    'target_revenue' => $formattedTotal
                ]);
            } elseif (request()->data == 'table') {
                return DataTables::of($data)
                    ->addColumn('pay_amount', function ($data) {
                        return 'Rp' . number_format($data->pay_amount, 0, ',', '.');
                    })
                    ->addColumn('date', function ($data) {
                        return Carbon::parse($data->created_at)->translatedFormat('d F Y H:i:s');
                    })
                    ->addColumn('status', function ($data) {
                        return $data->status == 'UNPAID' ? '<span class="badge badge-danger">Belum Lunas</span>' : '<span class="badge badge-success">Lunas</span>';
                    })
                    ->addColumn('student', function ($data) {
                        $studentName = $data->student?->name ? $data->student->name : '-';
                        $className = $data->student?->classroom?->name ? $data->student?->classroom->name : '-';

                        // Check if avatar exists, if not, use default avatar
                        $avatarUrl = $data->student?->avatar ? $data->student->avatar : asset('assets/media/avatars/default.png');

                        // Return HTML structure for the card with avatar, name, and class
                        return '<div class="student-card" style="display: flex; align-items: center; gap: 10px;">
                        <img src="' . $avatarUrl . '" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <div>
                            <div><strong>' . $studentName . '</strong></div>
                            <div>' . $className . '</div>
                        </div>
                    </div>';
                    })
                    ->addColumn('admin', function ($data) {
                        $adminAvatar = $data->admins?->name ? $data->admins->name : '-';
                        $role = $data->admins?->role ? $data->admins?->role->name : '-';

                        // Check if avatar exists, if not, use default avatar
                        $avatarUrl = $data->admins?->avatar ? $data->admins->avatar : asset('assets/media/avatars/default.png');

                        // Return HTML structure for the card with avatar, name, and class
                        return '<div class="student-card" style="display: flex; align-items: center; gap: 10px;">
                        <img src="' . $avatarUrl . '" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <div>
                            <div><strong>' . $adminAvatar . '</strong></div>
                            <div>' . $role . '</div>
                        </div>
                    </div>';
                    })
                    ->addColumn('action', function ($data) {
                        $actionDelete = route('report-transaction.destroy', $data->id);
                        return "<div class='d-flex gap-2 flex-nowrap justify-content-center'>" .
                            // add icon print invoice
                            view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Laporan Transaksi']) .
                            "</div>";
                        // add delete action
                    })
                    ->editColumn('pay_amount', function ($data) {
                        return 'Rp' . number_format($data->pay_amount, 0, ',', '.');
                    })
                    ->rawColumns(['date', 'action', 'student', 'status', 'admin'])
                    ->make(true);
            }
        }

        $schools = School::orderBy('name')->get();
        return view('admins.pos-transaction.index', compact('schools'));
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

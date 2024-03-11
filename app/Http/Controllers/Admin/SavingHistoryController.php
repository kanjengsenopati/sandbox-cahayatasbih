<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Student;
use App\Models\SaldoHistory;
use Illuminate\Http\Request;
use App\Models\SavingHistory;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SavingHistoryRequest;

class SavingHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = SavingHistory::with('student')->latest()->get();
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
        return view('admins.saving-history.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admins.saving-history.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SavingHistoryRequest $request)
    {

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
}

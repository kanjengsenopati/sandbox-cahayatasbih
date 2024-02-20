<?php

namespace App\Http\Controllers\Admin;

use App\Models\SaldoHistory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaldoHistoryRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaldoHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
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
                // ->addColumn('action', function ($data) {
                //     $actionEdit = route('saldo-history.edit', $data->id);
                //     $actionDelete = route('saldo-history.destroy', $data->id);
                //     return "<div class='d-flex justify-content-center'>" .
                //         view('components.action.edit', ['action' => $actionEdit]) .
                //         view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                //         "</div>";
                // })
                ->rawColumns(['amount', 'status'])
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
}

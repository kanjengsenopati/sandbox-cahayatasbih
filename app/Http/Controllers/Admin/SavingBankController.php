<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bank;
use App\Models\School;
use App\Models\TopupBank;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\SavingBankRequest;

class SavingBankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Tabungan Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = School::with(['topupBank' => function ($query) {
                $query->where('type', TopupBank::TYPE_SAVING);
            }])->orderBy('name', 'asc');
            return DataTables::of($data)
                ->addColumn('banks', function ($query) {
                    if ($query->topupBank->isEmpty()) {
                        return "<span class='badge bg-danger'>Belum ada bank</span>";
                    } else {
                        $bank = "";
                        foreach ($query->topupBank as $value) {
                            $bank .= "<span class='badge bg-success m-1'>{$value->bank?->name} - {$value->bank?->account_number}</span>";
                        }
                        return $bank;
                    }
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('saving-bank.edit', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Tabungan Santri']) .
                        "</div>";
                })
                ->rawColumns(['banks', 'action'])
                ->make(true);
        }
        return view('admins.saving-bank.index');
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
        $school = School::findOrFail($id);
        $banks = Bank::orderBy('name', 'asc')->get();
        $selectedBanks = $school->topupBank->where('type', TopupBank::TYPE_SAVING)->pluck('bank_id')->toArray();
        return view('admins.saving-bank.create-edit', compact('school', 'banks', 'selectedBanks'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SavingBankRequest $request, string $id)
    {
        try {
            DB::beginTransaction(); // Begin transaction

            $school = School::findOrFail($id);
            $validated = $request->validated();

            // Delete existing topupBank records related to the school
            $school->topupBank()->where('type', TopupBank::TYPE_SAVING)->delete();

            // Create new topupBank records based on validated bank_ids
            foreach ($validated['bank_ids'] as $bankId) {
                $school->topupBank()->create(['bank_id' => $bankId, 'type' => TopupBank::TYPE_SAVING]);
            }

            DB::commit(); // Commit the transaction

            return redirect()->route('saving-bank.index')->with('success', 'Data Bank Tabungan berhasil diubah');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if an exception occurred
            Log::error('Error updating bank tabungan: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

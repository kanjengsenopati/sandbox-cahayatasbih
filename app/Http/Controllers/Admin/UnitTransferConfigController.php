<?php

namespace App\Http\Controllers\Admin;

use App\Models\School;
use App\Models\BillType;
use App\Models\Classroom;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UnitTransferConfig;
use Yajra\DataTables\Facades\DataTables;

class UnitTransferConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = UnitTransferConfig::with(['fromSchool', 'toSchool', 'toClassroom', 'billType'])->latest();
            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => route('unit-transfer-config.edit', $data->id), 'name' => 'Setting Lanjut Unit']) .
                        view('components.action.delete', ['action' => route('unit-transfer-config.destroy', $data->id), 'id' => $data->id, 'name' => 'Setting Lanjut Unit']) .
                        "</div>";
                })
                ->editColumn('is_active', function ($data) {
                    return $data->is_active 
                        ? '<span class="badge badge-light-success">Aktif</span>' 
                        : '<span class="badge badge-light-danger">Tidak Aktif</span>';
                })
                ->addColumn('formatted_amount', function ($data) {
                    return 'Rp ' . number_format($data->amount, 0, ',', '.');
                })
                ->editColumn('eligible_class_level', function ($data) {
                    return $data->eligible_class_level ?: '<span class="text-muted">Semua</span>';
                })
                ->rawColumns(['action', 'is_active', 'eligible_class_level'])
                ->make(true);
        }

        return view('admins.unit-transfer-config.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schools = School::orderBy('name', 'asc')->get();
        $classrooms = Classroom::orderBy('name', 'asc')->get();
        $billTypes = BillType::orderBy('name', 'asc')->get();
        return view('admins.unit-transfer-config.create', compact('schools', 'classrooms', 'billTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_school_id' => 'required|exists:schools,id',
            'to_school_id' => 'required|exists:schools,id|different:from_school_id',
            'to_classroom_id' => 'required|exists:classrooms,id',
            'bill_type_id' => 'required|exists:bill_types,id',
            'amount' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        UnitTransferConfig::create($validated);

        return redirect()->route('unit-transfer-config.index')->with('success', 'Setting Lanjut Unit berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $unitTransferConfig = UnitTransferConfig::findOrFail($id);
        $schools = School::orderBy('name', 'asc')->get();
        $classrooms = Classroom::where('school_id', $unitTransferConfig->to_school_id)->orderBy('name', 'asc')->get();
        $billTypes = BillType::orderBy('name', 'asc')->get();
        
        return view('admins.unit-transfer-config.edit', compact('unitTransferConfig', 'schools', 'classrooms', 'billTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $unitTransferConfig = UnitTransferConfig::findOrFail($id);

        $validated = $request->validate([
            'from_school_id' => 'required|exists:schools,id',
            'to_school_id' => 'required|exists:schools,id|different:from_school_id',
            'to_classroom_id' => 'required|exists:classrooms,id',
            'bill_type_id' => 'required|exists:bill_types,id',
            'amount' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $unitTransferConfig->update($validated);

        return redirect()->route('unit-transfer-config.index')->with('success', 'Setting Lanjut Unit berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $unitTransferConfig = UnitTransferConfig::findOrFail($id);
        $unitTransferConfig->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menghapus data'
        ]);
    }
}

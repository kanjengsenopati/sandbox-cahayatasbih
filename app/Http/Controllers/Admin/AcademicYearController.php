<?php

namespace App\Http\Controllers\Admin;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\AcademicYearRequest;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Tahun Ajaran')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $data = AcademicYear::latest();
            return DataTables::of($data)
                ->addColumn('status', function ($data) {
                    return $data->is_active ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-danger">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionStatus = route('academic-year.status', $data->id);
                    $actionEdit = route('academic-year.edit', $data->id);
                    $actionDelete = route('academic-year.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.status', ['action' => $actionStatus, 'status' => $data->is_active, 'id' => $data->id, 'name' => 'Tahun Ajaran']) .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Tahun Ajaran']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Tahun Ajaran']) .
                        "</div>";
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('admins.academic-year.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Tahun Ajaran')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.academic-year.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AcademicYearRequest $request)
    {
        if (!Auth::user()->can('Create Tahun Ajaran')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        AcademicYear::create($request->validated());
        return redirect()->route('academic-year.index')->with('success', 'Tahun Akademik berhasil ditambahkan');
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
    public function edit(AcademicYear $academicYear)
    {
        if (!Auth::user()->can('Edit Tahun Ajaran')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.academic-year.create-edit', compact('academicYear'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AcademicYearRequest $request, AcademicYear $academicYear)
    {
        if (!Auth::user()->can('Edit Tahun Ajaran')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $academicYear->update($request->validated());
        return redirect()->route('academic-year.index')->with('success', 'Tahun Akademik berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicYear $academicYear)
    {
        if (!Auth::user()->can('Delete Tahun Ajaran')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $academicYear->delete();
        return redirect()->route('academic-year.index')->with('success', 'Tahun Akademik berhasil dihapus');
    }

    public function status(string $id)
    {
        if (!Auth::user()->can('Edit Tahun Ajaran')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        try {
            DB::beginTransaction();

            $academicYear = AcademicYear::findOrFail($id);

            // Non-aktifkan semua tahun akademik yang aktif
            AcademicYear::where('is_active', true)->update([
                'is_active' => false
            ]);

            $academicYear->update([
                'is_active' => !$academicYear->is_active
            ]);

            DB::commit();
            return redirect()->route('academic-year.index')
                ->with('success', 'Status Tahun Akademik berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return redirect()->route('academic-year.index')
                ->with('error', 'Terjadi kesalahan. Status Tahun Akademik tidak dapat diperbarui.');
        }
    }
}

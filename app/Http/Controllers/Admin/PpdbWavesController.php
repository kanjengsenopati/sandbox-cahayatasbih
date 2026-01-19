<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ppdb;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PpdbWavesRequest;
use App\Models\AcademicYear;
use App\Models\PpdbTrack;
use App\Models\PpdbWaves;
use Illuminate\Support\Facades\Auth;

class PpdbWavesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = PpdbWaves::with('academicYear')->latest();
            return DataTables::of($data)
                ->addColumn('period', function ($data) {
                    return date('d M Y', strtotime($data->start_date)) . ' - ' . date('d M Y', strtotime($data->end_date));
                })
                ->addColumn('status', function ($data) {
                    return $data->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Non Aktif</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('ppdb-waves.edit', $data->id);
                    $actionDelete = route('ppdb-waves.destroy', $data->id);
                    $actionShow = route('ppdb-waves.show', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.show', ['action' => $actionShow, 'label' => 'Kelas']) .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Sekolah']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Sekolah']) .
                        "</div>";
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('admins.ppdb-waves.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        return view('admins.ppdb-waves.create-edit', compact('academicYears'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PpdbWavesRequest $request)
    {
        $data = $request->validated();
        PpdbWaves::create($data);
        return redirect()->route('ppdb-waves.index')->with('success', 'Gelombang PPDB berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $ppdbWaves = PpdbWaves::with('academicYear')->findOrFail($id);
        $ppdbTracks = PpdbTrack::with(['school'])->where('ppdb_wave_id', $ppdbWaves->id)->get();
        $ppdbTrackTypes = (new PpdbTrack())->getListRegistrationTypes();
        return view('admins.ppdb-waves.show', compact('ppdbWaves', 'ppdbTracks', 'ppdbTrackTypes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $ppdbWaves = PpdbWaves::findOrFail($id);
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        return view('admins.ppdb-waves.create-edit', compact('ppdbWaves', 'academicYears'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PpdbWavesRequest $request, PpdbWaves $ppdbWaves)
    {
        $data = $request->validated();
        $ppdbWaves->update($data);
        return redirect()->route('ppdb-waves.index')->with('success', 'Gelombang PPDB berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $ppdbWaves = PpdbWaves::findOrFail($id);
        $ppdbWaves->delete();
        return redirect()->route('ppdb-waves.index')->with('success', 'Gelombang PPDB berhasil dihapus');
    }
}

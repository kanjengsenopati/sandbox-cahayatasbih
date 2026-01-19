<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\School;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\ScheduleRequest;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Jadwal')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $schoolIds = Auth::user()?->adminSchool?->pluck('school_id');
            $data = Schedule::with('school')
                // cari yang punya school_id atau yang school_id nya null
                ->whereIn('school_id', $schoolIds)
                ->orWhere('school_id', null)
                ->orderBy('date', 'desc');

            return DataTables::of($data)
                ->editColumn('type', function ($data) {
                    return $data->type == Schedule::TYPE_ALL ? '<span class="badge badge-success">Semua</span>' : '<span class="badge badge-primary">Sekolah</span>';
                })
                ->editColumn('date', function ($data) {
                    return Carbon::parse($data->date)->locale('id')->translatedFormat('l, d F Y');
                })
                ->editColumn('school_id', function ($data) {
                    return $data->school_id ? '<span class="badge badge-primary">' . $data->school->name . '</span>' : '<span class="badge badge-secondary">Semua Sekolah</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('schedule.edit', $data->id);
                    $actionDelete = route('schedule.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Jadwal']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Jadwal']) .
                        "</div>";
                })
                ->rawColumns(['action', 'school_id', 'type'])
                ->make(true);
        }
        return view('admins.schedule.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Jadwal')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $schools = School::hasSchool()->OrderBy('name', 'asc')->get();
        return view('admins.schedule.create-edit', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ScheduleRequest $request)
    {
        if (!Auth::user()->can('Create Jadwal')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        Schedule::create($data);
        return redirect()->route('schedule.index')->with('success', 'Data berhasil ditambahkan');
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
    public function edit(Schedule $schedule)
    {
        if (!Auth::user()->can('Edit Jadwal')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $schools = School::hasSchool()->OrderBy('name', 'asc')->get();
        return view('admins.schedule.create-edit', compact('schedule', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ScheduleRequest $request, Schedule $schedule)
    {
        if (!Auth::user()->can('Edit Jadwal')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        $schedule->update($data);
        return redirect()->route('schedule.index')->with('success', 'Data berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        if (!Auth::user()->can('Delete Jadwal')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $schedule->delete();
        return redirect()->route('schedule.index')->with('success', 'Data berhasil dihapus');
    }
}

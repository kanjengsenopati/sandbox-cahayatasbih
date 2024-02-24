<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScheduleRequest;
use App\Models\Schedule;
use App\Models\School;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Schedule::with('school')->orderBy('date', 'desc')->get();
            return DataTables::of($data)
                ->editColumn('type', function ($data) {
                    return $data->type == Schedule::TYPE_ALL ? '<span class="badge badge-success">Semua</span>' : '<span class="badge badge-primary">Sekolah</span>';
                })
                ->editColumn('date', function ($data) {
                    return $data->date->format('d F Y');
                })
                ->editColumn('school_id', function ($data) {
                    return $data->school_id ? '<span class="badge badge-primary">' . $data->school->name . '</span>' : '<span class="badge badge-secondary">Semua Sekolah</span>';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('schedule.edit', $data->id);
                    $actionDelete = route('schedule.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
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
        $schools = School::orderBy('name')->get();
        return view('admins.schedule.create-edit', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ScheduleRequest $request)
    {
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
        $schools = School::orderBy('name')->get();
        return view('admins.schedule.create-edit', compact('schedule', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ScheduleRequest $request, Schedule $schedule)
    {
        $data = $request->validated();
        $schedule->update($data);
        return redirect()->route('schedule.index')->with('success', 'Data berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('schedule.index')->with('success', 'Data berhasil dihapus');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;

class LogActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Activity::latest()->where('causer_id', '!=', NULL)->get();
        if (request()->ajax()) {
            return DataTables::of($data)
                ->editColumn('created_at', function ($data) {
                    return date('d/m/y H:i', strtotime($data->created_at));
                })
                ->addColumn('name', function ($data) {
                    return $data->causer_type::find($data->causer_id)->name ?? '<i class="text-danger">user deleted</i>';
                })
                ->editColumn('description', function ($data) {
                    if ($data->event == 'updated' || $data->event == 'deleted') {
                        $description = $data->description . ' <a type="button" onclick="see_previous_data(' . $data->id . ')" class="text-primary font-italic">See Previous Data</a>';
                    } else {
                        $description = $data->description;
                    }
                    return $description;
                })
                ->rawColumns(['description', 'name'])
                ->make(true);
        }
        return view('admins.log-activity.index');
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
        $data = Activity::find($id)->properties;
        $data = json_decode($data);
        $list = '';
        foreach ($data->old as $key => $value) {
            if ($value) {
                $list .= "<tr>
                    <td class='fw-bold' width='15%'>" . ucwords(str_replace("_", " ", $key)) . "</td>
                    <td class='text-center' width='3%'>:</td>
                    <td class='ps-2'>{$value}</td>
                </tr>";
            }
        }
        return $list;
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
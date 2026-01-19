<?php

namespace App\Http\Controllers\User;

use App\Models\Student;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class WaliSettingLimitSaldoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Student::with('classroom.school')->where('user_id', auth('wali')->id())->orderBy('name')->get();
            return DataTables::of($data)
                ->editColumn('saldo', function ($data) {
                    return 'Rp' . number_format($data->saldo, 0, ',', '.');
                })
                ->editColumn('daily_limit', function ($data) {
                    return 'Rp' . number_format($data->daily_limit, 0, ',', '.');
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('wali.setting-limit-saldo.edit', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('users.setting-limit-saldo.index');
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
        $student = Student::findOrFail($id);
        return view('users.setting-limit-saldo.create-edit', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'daily_limit' => 'required|numeric|min:0',
        ]);

        $student = Student::findOrFail($id);
        $student->update($data);
        return redirect()->route('wali.setting-limit-saldo.index')->with('success', 'Limit saldo berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

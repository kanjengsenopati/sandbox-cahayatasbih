<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\PpdbRegistration;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class PpdbHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = PpdbRegistration::where('user_id', auth('wali')->user()->id)
                ->latest()
                ->get();
            return DataTables::of($data)
                ->addColumn('ppdb_name', function ($data) {
                    return $data->ppdb->name ?? '-';
                })
                ->addColumn('school_name', function ($data) {
                    return $data->ppdb->school->name ?? '-';
                })
                ->addColumn('student_name', function ($data) {
                    return $data->ppdbStudents->first()->name ?? '-';
                })
                ->addColumn('action', function ($data) {
                    $actionPay = route('wali.ppdb-history.pay', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.show', ['action' => $actionPay, 'label' => 'Bayar']) .
                        "</div>";
                })
                ->rawColumns(['action', 'ppdb_name', 'school_name', 'student_name'])
                ->make(true);
        }
        return view('users.ppdb-history.index');
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

    /**
     * Pay the specified resource from storage.
     */
    public function pay(string $id)
    {
        $ppdbRegistration = PpdbRegistration::findOrFail($id);
        return view('users.ppdb-history.pay', compact('ppdbRegistration'));
    }
}

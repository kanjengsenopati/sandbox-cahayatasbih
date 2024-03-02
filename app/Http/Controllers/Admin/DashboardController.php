<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Client;
use App\Models\Article;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Http\Request;
use App\Models\HistoryDownload;
use Yajra\DataTables\DataTables;
use App\Models\WhiteBlowingSystem;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $total_students = Student::count();
        $total_classes = Classroom::count();
        $data = [
            'total_students' => $total_students,
            'total_classes' => $total_classes,
        ];
        return view('admins.dashboard.index', compact('data'));
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
}

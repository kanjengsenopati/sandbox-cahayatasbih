<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Schedule::with('school')->orderBy('date', 'desc');

        $student = Student::find(request()->student_id);

        if ($student) {
            $query->where(function ($q) use ($student) {
                $q->where('type', Schedule::TYPE_ALL)
                    ->orWhere(function ($q) use ($student) {
                        $q->where('type', Schedule::TYPE_SCHOOL)
                            ->where('school_id', $student->school_id);
                    });
            });
        }

        $schedules = $query->get();

        return $this->postSuccessResponse("Berhasil mengambil Data Jadwal", $schedules);
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

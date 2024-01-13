<?php

namespace App\Http\Controllers\Admin;

use App\Models\Student;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentRequest;
use App\Models\School;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Student::with('user')->latest()->get();
            return DataTables::of($data)
                ->addColumn('date_of_birth', function ($data) {
                    $birthPlace = $data->born_place ? $data->born_place . ', ' : '';
                    $dateOfBirth = $data->birth_date ? date('d-m-Y', strtotime($data->birth_date)) : '';
                    return $birthPlace . $dateOfBirth;
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('student.edit', $data->id);
                    $actionDelete = route('student.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admins.student.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schools = School::orderBy('name')->get();
        return view('admins.student.create-edit', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('avatar')) {
            $data['avatar'] = 'storage/' . $request->file('avatar')->store('images/avatar', 'public');
        }
        Student::create($data);
        return redirect()->route('student.index')->with('success', 'Siswa berhasil ditambahkan');
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
    public function edit(Student $student)
    {
        $schools = School::orderBy('name')->get();
        return view('admins.student.create-edit', compact('student', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentRequest $request, Student $student)
    {
        $data = $request->validated();
        if ($request->hasFile('avatar')) {
            file_exists($student->avatar) ? unlink($student->avatar) : '';
            $data['avatar'] = 'storage/' . $request->file('avatar')->store('images/avatar', 'public');
        }
        $student->update($data);
        return redirect()->route('student.index')->with('success', 'Siswa berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        file_exists($student->avatar) ? unlink($student->avatar) : '';
        $student->delete();
        return redirect()->route('student.index')->with('success', 'Siswa berhasil dihapus');
    }
}

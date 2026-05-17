<?php

namespace App\Http\Controllers\Admin;

use App\Models\Asrama;
use App\Models\Student;
use App\Models\Officer;
use App\Models\Admin;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AsramaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Asrama')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $data = Asrama::with('hostAdmin')->withCount('students')->latest();
            return DataTables::of($data)
                ->addColumn('host_name', function ($row) {
                    return $row->hostAdmin ? $row->hostAdmin->name : '-';
                })
                ->addColumn('host_phone', function ($row) {
                    return $row->hostAdmin ? $row->hostAdmin->phone : '-';
                })
                ->addColumn('student_count', function ($row) {
                    return $row->students_count . ' Santri';
                })
                ->addColumn('btnAction', function ($row) {
                    $actionEdit = route('asrama.edit', $row->id);
                    $actionDelete = route('asrama.destroy', $row->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Asrama']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $row->id, 'name' => 'Asrama']) .
                        "</div>";
                })
                ->rawColumns(['btnAction'])
                ->make(true);
        }

        return view('admins.asrama.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Asrama')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $officers = Officer::where('is_active', 1)->orderBy('name', 'asc')->get();
        
        // Fetch all active students that don't have an asrama yet, or we can fetch all active students
        $students = Student::where('status', Student::STATUS_ACTIVE)
            ->orderBy('name', 'asc')
            ->get();

        return view('admins.asrama.create-edit', compact('officers', 'students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Asrama')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:asramas,name',
            'host_type' => 'required|in:existing,new',
            'officer_id' => 'required_if:host_type,existing|nullable|string',
            'new_officer_name' => 'required_if:host_type,new|nullable|string|max:255',
            'new_officer_phone' => 'required_if:host_type,new|nullable|string|max:20',
            'new_officer_position' => 'required_if:host_type,new|nullable|string|max:255',
            'new_officer_duty' => 'required_if:host_type,new|nullable|string|max:255',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'string|exists:students,id',
        ], [
            'name.unique' => 'Nama asrama sudah terdaftar.',
            'name.required' => 'Nama asrama wajib diisi.',
        ]);

        try {
            DB::beginTransaction();

            $hostAdminId = null;

            if ($request->input('host_type') === 'existing') {
                $officer = Officer::findOrFail($request->input('officer_id'));
                $hostAdminId = $this->getOrCreateAdminForOfficer($officer);
            } else {
                // Create new officer record
                $officer = Officer::create([
                    'name' => $request->input('new_officer_name'),
                    'phone' => $request->input('new_officer_phone'),
                    'position' => $request->input('new_officer_position'),
                    'duty' => $request->input('new_officer_duty'),
                    'is_active' => 1,
                ]);
                $hostAdminId = $this->getOrCreateAdminForOfficer($officer);
            }

            // Create Asrama record
            $asrama = Asrama::create([
                'name' => $request->input('name'),
                'host_admin_id' => $hostAdminId,
            ]);

            // Sync assigned students
            if ($request->has('student_ids')) {
                Student::whereIn('id', $request->input('student_ids'))->update([
                    'asrama_id' => $asrama->id,
                ]);
            }

            DB::commit();

            return redirect()->route('asrama.index')->with('success', 'Data asrama berhasil ditambahkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (!Auth::user()->can('Edit Asrama')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $asrama = Asrama::with('students')->findOrFail($id);
        $officers = Officer::where('is_active', 1)->orderBy('name', 'asc')->get();
        
        // Find which officer is currently linked to the host_admin_id
        $currentOfficerId = null;
        if ($asrama->host_admin_id) {
            $admin = Admin::find($asrama->host_admin_id);
            if ($admin) {
                $officer = Officer::where('phone', $admin->phone)->first();
                if ($officer) {
                    $currentOfficerId = $officer->id;
                }
            }
        }

        // Fetch all active students
        $students = Student::where('status', Student::STATUS_ACTIVE)
            ->orderBy('name', 'asc')
            ->get();

        // Get currently assigned student IDs
        $assignedStudentIds = $asrama->students->pluck('id')->toArray();

        return view('admins.asrama.create-edit', compact('asrama', 'officers', 'students', 'assignedStudentIds', 'currentOfficerId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!Auth::user()->can('Edit Asrama')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $asrama = Asrama::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:asramas,name,' . $asrama->id,
            'host_type' => 'required|in:existing,new',
            'officer_id' => 'required_if:host_type,existing|nullable|string',
            'new_officer_name' => 'required_if:host_type,new|nullable|string|max:255',
            'new_officer_phone' => 'required_if:host_type,new|nullable|string|max:20',
            'new_officer_position' => 'required_if:host_type,new|nullable|string|max:255',
            'new_officer_duty' => 'required_if:host_type,new|nullable|string|max:255',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'string|exists:students,id',
        ], [
            'name.unique' => 'Nama asrama sudah terdaftar.',
            'name.required' => 'Nama asrama wajib diisi.',
        ]);

        try {
            DB::beginTransaction();

            $hostAdminId = null;

            if ($request->input('host_type') === 'existing') {
                $officer = Officer::findOrFail($request->input('officer_id'));
                $hostAdminId = $this->getOrCreateAdminForOfficer($officer);
            } else {
                // Create new officer record
                $officer = Officer::create([
                    'name' => $request->input('new_officer_name'),
                    'phone' => $request->input('new_officer_phone'),
                    'position' => $request->input('new_officer_position'),
                    'duty' => $request->input('new_officer_duty'),
                    'is_active' => 1,
                ]);
                $hostAdminId = $this->getOrCreateAdminForOfficer($officer);
            }

            // Update Asrama
            $asrama->update([
                'name' => $request->input('name'),
                'host_admin_id' => $hostAdminId,
            ]);

            // Sync assigned students:
            // 1. Unset asrama_id for students previously linked to this asrama but not selected anymore
            $newStudentIds = $request->input('student_ids', []);
            
            Student::where('asrama_id', $asrama->id)
                ->whereNotIn('id', $newStudentIds)
                ->update(['asrama_id' => null]);

            // 2. Set asrama_id for newly selected students
            if (!empty($newStudentIds)) {
                Student::whereIn('id', $newStudentIds)->update([
                    'asrama_id' => $asrama->id,
                ]);
            }

            // Explicitly trigger updating to propagate flat fields changes to students
            $asrama->students()->update([
                'asrama_name' => $asrama->name,
                'asrama_host_id' => $asrama->host_admin_id,
            ]);

            DB::commit();

            return redirect()->route('asrama.index')->with('success', 'Data asrama berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!Auth::user()->can('Delete Asrama')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        try {
            DB::beginTransaction();

            $asrama = Asrama::findOrFail($id);

            // Dissociate child students
            Student::where('asrama_id', $asrama->id)->update([
                'asrama_id' => null,
            ]);

            $asrama->delete();

            DB::commit();

            return redirect()->route('asrama.index')->with('success', 'Data asrama berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Helper to get or dynamically create an Admin account linked to an Officer's phone.
     */
    private function getOrCreateAdminForOfficer(Officer $officer)
    {
        // Search by phone
        $admin = Admin::where('phone', $officer->phone)->first();

        if (!$admin) {
            // Create a new Admin account
            $emailName = Str::slug($officer->name, '');
            $email = $emailName . '@cahayatasbih.com';
            
            // Handle email collision by appending random string if needed
            $count = Admin::where('email', $email)->count();
            if ($count > 0) {
                $email = $emailName . rand(100, 999) . '@cahayatasbih.com';
            }

            // Search for "PETUGAS PIKET" role first to assign it both in admins table flat column and Spatie pivot table
            $role = Role::where('name', 'like', '%PETUGAS%')->first() ?? Role::first();
            $roleId = $role ? $role->id : null;

            $admin = Admin::create([
                'name' => $officer->name,
                'email' => $email,
                'phone' => $officer->phone,
                'password' => bcrypt('12345678'),
                'avatar' => 'assets/media/avatars/150-26.jpg',
                'is_active' => 1,
                'role_id' => $roleId,
            ]);

            if ($role) {
                $admin->assignRole($role);
            }
        } else {
            // Make sure the existing admin profile matches current officer name and status
            $admin->update([
                'name' => $officer->name,
                'is_active' => 1,
            ]);
        }

        return $admin->id;
    }
}

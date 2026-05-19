<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Classroom;
use App\Models\ApplicationMenu;
use App\Models\ApplicationMenuScope;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\ApplicationMenuRequest;

class ApplicationMenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = ApplicationMenu::with('scopes.school')->latest()->get();
            return DataTables::of($data)
                ->addColumn('status', function ($data) {
                    return $data->status ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-danger">Tidak Aktif</span>';
                })
                ->addColumn('scope', function ($data) {
                    if ($data->scopes->isEmpty()) {
                        return '<span class="badge badge-light-primary">🌐 Semua Unit</span>';
                    }
                    return $data->scopes->map(function ($scope) {
                        $label = $scope->school->name ?? 'Unit';
                        if ($scope->class_level) {
                            $label .= ' · Kelas ' . $scope->class_level;
                        }
                        return '<span class="badge badge-light-info me-1 mb-1">' . e($label) . '</span>';
                    })->join(' ');
                })
                ->addColumn('action', function ($data) {
                    $actionStatus = route('application-menu.status', $data->id);
                    $actionEdit = route('application-menu.edit', $data->id);
                    $actionDelete = route('application-menu.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.status', ['action' => $actionStatus, 'status' => $data->is_active, 'id' => $data->id, 'name' => 'Menu Aplikasi']) . '&nbsp;' .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Menu Aplikasi']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Menu Aplikasi']) .
                        "</div>";
                })
                ->rawColumns(['action', 'status', 'scope'])
                ->make(true);
        }
        return view('admins.application-menu.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $schools = School::orderBy('name')->get();
        return view('admins.application-menu.create-edit', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ApplicationMenuRequest $request)
    {
        if (!Auth::user()->can('Create Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $menu = ApplicationMenu::create($request->validated());
        $this->syncScopes($menu, $request);
        return redirect()->route('application-menu.index')->with('success', 'Menu Aplikasi berhasil ditambahkan');
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
    public function edit(ApplicationMenu $applicationMenu)
    {
        if (!Auth::user()->can('Edit Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $schools = School::orderBy('name')->get();
        $applicationMenu->load('scopes');
        return view('admins.application-menu.create-edit', compact('applicationMenu', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ApplicationMenuRequest $request, ApplicationMenu $applicationMenu)
    {
        if (!Auth::user()->can('Edit Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $applicationMenu->update($request->validated());
        $this->syncScopes($applicationMenu, $request);
        return redirect()->route('application-menu.index')->with('success', 'Menu Aplikasi berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApplicationMenu $applicationMenu)
    {
        if (!Auth::user()->can('Delete Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $applicationMenu->delete();
        return redirect()->route('application-menu.index')->with('success', 'Menu Aplikasi berhasil dihapus');
    }

    public function status(string $id)
    {
        if (!Auth::user()->can('Edit Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $applicationMenu = ApplicationMenu::find($id);
        $applicationMenu->status = !$applicationMenu->status;
        $applicationMenu->save();
        return redirect()->route('application-menu.index')->with('success', 'Status Menu Aplikasi berhasil diperbarui');
    }

    /**
     * AJAX: Ambil daftar jenjang kelas berdasarkan school_id.
     */
    public function getClassLevels(Request $request)
    {
        $schoolIds = $request->input('school_ids', []);
        if (empty($schoolIds)) {
            return response()->json([]);
        }

        // Ambil semua nama kelas dari unit yang dipilih, lalu ekstrak jenjangnya
        $classrooms = Classroom::whereIn('school_id', $schoolIds)->pluck('name');

        $levels = $classrooms->map(function ($name) {
            if (preg_match('/^(VII|VIII|IX|X{1,2}I{0,2}|I{1,3}V?|[0-9]+)/', strtoupper($name), $matches)) {
                return $matches[1];
            }
            return null;
        })->filter()->unique()->sort()->values();

        return response()->json($levels);
    }

    /**
     * Sinkronisasi scope menu berdasarkan input form.
     */
    private function syncScopes(ApplicationMenu $menu, Request $request)
    {
        // Hapus semua scope lama
        $menu->scopes()->delete();

        // Jika toggle scope tidak aktif, biarkan kosong (menu = global)
        if (!$request->has('enable_scope') || !$request->input('enable_scope')) {
            return;
        }

        $schoolIds = $request->input('scope_schools', []);
        $classLevels = $request->input('scope_class_levels', []);

        if (empty($schoolIds)) {
            return;
        }

        foreach ($schoolIds as $schoolId) {
            if (empty($classLevels)) {
                // Scope unit saja tanpa jenjang kelas tertentu
                ApplicationMenuScope::create([
                    'application_menu_id' => $menu->id,
                    'school_id' => $schoolId,
                    'class_level' => null,
                ]);
            } else {
                foreach ($classLevels as $level) {
                    ApplicationMenuScope::create([
                        'application_menu_id' => $menu->id,
                        'school_id' => $schoolId,
                        'class_level' => $level,
                    ]);
                }
            }
        }
    }
}

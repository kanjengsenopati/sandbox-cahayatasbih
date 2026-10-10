<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Classroom;
use App\Models\Officer;
use App\Models\ApplicationMenu;
use App\Models\ApplicationMenuScope;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            session()->save();
            $data = ApplicationMenu::with(['scopes.school', 'officer.admin'])
                ->orderBy('order', 'asc')
                ->orderBy('created_at', 'asc');
            return DataTables::of($data)
                ->addColumn('order', function ($data) {
                    return '
                        <div class="d-flex align-items-center gap-1">
                            <input type="number" min="1" class="form-control form-control-sm form-control-solid text-center px-1 py-1 fw-bolder text-primary menu-order-input" 
                                style="width: 55px; font-size: 13px;" 
                                data-id="' . $data->id . '" 
                                data-original="' . $data->order . '" 
                                value="' . $data->order . '" />
                            <div class="d-flex flex-column gap-0">
                                <button type="button" class="btn btn-icon btn-xs btn-light-primary py-0 px-1 btn-move-order" data-id="' . $data->id . '" data-direction="up" title="Geser ke Atas" style="height: 16px; width: 22px;">
                                    <i class="fas fa-chevron-up fs-9"></i>
                                </button>
                                <button type="button" class="btn btn-icon btn-xs btn-light-primary py-0 px-1 btn-move-order" data-id="' . $data->id . '" data-direction="down" title="Geser ke Bawah" style="height: 16px; width: 22px;">
                                    <i class="fas fa-chevron-down fs-9"></i>
                                </button>
                            </div>
                        </div>
                    ';
                })
                ->addColumn('name', function ($data) {
                    $html = '<div class="d-flex flex-column">';
                    $html .= '<span class="text-gray-800 fw-bolder mb-1">' . e($data->name) . '</span>';
                    if ($data->type === 'whatsapp') {
                        $html .= '<span class="text-muted fs-8"><i class="fab fa-whatsapp text-success me-1"></i> WhatsApp: ' . e($data->wa_number ?? '-');
                        if ($data->officer) {
                            $html .= ' · <span class="badge badge-light-success py-0 px-1 fs-9">' . e($data->officer->name . ' (' . $data->officer->position . ')') . '</span>';
                        }
                        $html .= '</span>';
                    } elseif ($data->type === 'external') {
                        $html .= '<span class="text-muted fs-8"><i class="fas fa-external-link-alt text-primary me-1"></i> ' . e(\Illuminate\Support\Str::limit($data->url, 40)) . '</span>';
                    } else {
                        $html .= '<span class="text-muted fs-8"><i class="fas fa-code text-secondary me-1"></i> Flag: ' . e($data->flag) . '</span>';
                    }
                    $html .= '</div>';
                    return $html;
                })
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
                ->rawColumns(['action', 'status', 'scope', 'name', 'order'])
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
        $officers = Officer::with('admin')
            ->where('is_active', true)
            ->get()
            ->sortBy('name')
            ->values();
        $nextOrder = (ApplicationMenu::max('order') ?? 0) + 1;
        return view('admins.application-menu.create-edit', compact('schools', 'officers', 'nextOrder'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ApplicationMenuRequest $request)
    {
        if (!Auth::user()->can('Create Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $payload = $this->prepareMenuPayload($request);
        $menu = ApplicationMenu::create($payload);
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
        $officers = Officer::with('admin')
            ->where('is_active', true)
            ->get()
            ->sortBy('name')
            ->values();
        $applicationMenu->load(['scopes', 'officer.admin']);
        return view('admins.application-menu.create-edit', compact('applicationMenu', 'schools', 'officers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ApplicationMenuRequest $request, ApplicationMenu $applicationMenu)
    {
        if (!Auth::user()->can('Edit Menu Aplikasi')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $payload = $this->prepareMenuPayload($request);
        $applicationMenu->update($payload);
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

    /**
     * Format dan persiapkan payload menu aplikasi berdasarkan tipe tautan.
     */
    private function prepareMenuPayload(ApplicationMenuRequest $request): array
    {
        $data = $request->validated();
        $type = $request->input('type', 'internal');

        if ($type === 'whatsapp') {
            $rawPhone = $request->input('wa_number', '');
            $cleaned = preg_replace('/[^0-9]/', '', (string)$rawPhone);
            if (str_starts_with($cleaned, '08')) {
                $cleaned = '62' . substr($cleaned, 1);
            } elseif (str_starts_with($cleaned, '8')) {
                $cleaned = '62' . $cleaned;
            }

            $message = $request->input('wa_message') ?: "Assalamu'alaikum, saya wali santri [nama_wali], orang tua / wali dari [nama-santri dan kelas] ingin bertanya";

            $data['type'] = 'whatsapp';
            $data['officer_id'] = $request->input('officer_id') ?: null;
            $data['wa_number'] = $cleaned;
            $data['wa_message'] = $message;
            $data['url'] = "https://wa.me/{$cleaned}?text=" . urlencode($message);
            $data['icon'] = $request->input('icon') ?: 'MessageCircle';
        } elseif ($type === 'external') {
            $data['type'] = 'external';
            $data['officer_id'] = null;
            $data['url'] = $request->input('url');
            $data['wa_number'] = null;
            $data['wa_message'] = null;
            $data['icon'] = $request->input('icon') ?: 'ExternalLink';
        } else {
            $data['type'] = 'internal';
            $data['officer_id'] = null;
            $data['url'] = null;
            $data['wa_number'] = null;
            $data['wa_message'] = null;
            $data['icon'] = null;
        }

        if ($request->filled('order')) {
            $data['order'] = (int) $request->input('order');
        } elseif (!$request->route('application_menu')) {
            $data['order'] = (ApplicationMenu::max('order') ?? 0) + 1;
        }

        return $data;
    }

    /**
     * AJAX: Update urutan menu ke nomor spesifik dan re-index semua menu.
     */
    public function updateOrder(Request $request)
    {
        if (!Auth::user()->can('Edit Menu Aplikasi')) {
            return response()->json(['error' => 'Maaf, Anda tidak memiliki akses untuk tindakan ini.'], 403);
        }

        $request->validate([
            'id' => 'required|exists:application_menus,id',
            'order' => 'required|integer|min:1',
        ]);

        $targetId = $request->input('id');
        $newOrder = (int) $request->input('order');

        DB::transaction(function () use ($targetId, $newOrder) {
            $allMenus = ApplicationMenu::orderBy('order', 'asc')->orderBy('created_at', 'asc')->get();
            $targetMenu = $allMenus->firstWhere('id', $targetId);

            if (!$targetMenu) return;

            $remaining = $allMenus->filter(fn($m) => $m->id !== $targetId)->values();
            $insertIndex = max(0, min($newOrder - 1, $remaining->count()));
            $remaining->splice($insertIndex, 0, [$targetMenu]);

            foreach ($remaining as $idx => $m) {
                $seqOrder = $idx + 1;
                if ($m->order != $seqOrder) {
                    $m->update(['order' => $seqOrder]);
                }
            }
        });

        \Illuminate\Support\Facades\Cache::forever('wali_menus_version', time());

        return response()->json([
            'success' => true,
            'message' => 'Urutan menu berhasil diperbarui'
        ]);
    }

    /**
     * AJAX: Pindahkan urutan menu satu tingkat ke atas atau ke bawah (swap).
     */
    public function moveOrder(Request $request)
    {
        if (!Auth::user()->can('Edit Menu Aplikasi')) {
            return response()->json(['error' => 'Maaf, Anda tidak memiliki akses untuk tindakan ini.'], 403);
        }

        $request->validate([
            'id' => 'required|exists:application_menus,id',
            'direction' => 'required|in:up,down',
        ]);

        $targetId = $request->input('id');
        $direction = $request->input('direction');

        DB::transaction(function () use ($targetId, $direction) {
            $menus = ApplicationMenu::orderBy('order', 'asc')->orderBy('created_at', 'asc')->get();
            $currentIndex = $menus->search(fn($m) => $m->id === $targetId);

            if ($currentIndex === false) return;

            $targetIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

            if ($targetIndex < 0 || $targetIndex >= $menus->count()) {
                return; // Sudah berada di posisi paling atas atau paling bawah
            }

            $currentMenu = $menus[$currentIndex];
            $neighborMenu = $menus[$targetIndex];

            $tempOrder = $currentMenu->order;
            $currentMenu->order = $neighborMenu->order;
            $neighborMenu->order = $tempOrder;

            if ($currentMenu->order == $neighborMenu->order) {
                $currentMenu->order = $direction === 'up' ? $neighborMenu->order - 1 : $neighborMenu->order + 1;
            }

            $currentMenu->save();
            $neighborMenu->save();

            // Normalisasi seluruh urutan secara sekuensial
            $refreshed = ApplicationMenu::orderBy('order', 'asc')->orderBy('created_at', 'asc')->get();
            foreach ($refreshed as $idx => $m) {
                if ($m->order != $idx + 1) {
                    $m->update(['order' => $idx + 1]);
                }
            }
        });

        \Illuminate\Support\Facades\Cache::forever('wali_menus_version', time());

        return response()->json([
            'success' => true,
            'message' => 'Urutan menu berhasil dipindahkan'
        ]);
    }
}

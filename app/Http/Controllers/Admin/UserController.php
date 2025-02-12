<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Imports\UserImportData;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\Admin\UserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Cek izin pengguna
        if (!Auth::user()->can('Manage Wali Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        // Handle request AJAX untuk DataTables
        if (request()->ajax() && request()->query('type') === 'table') {
            return $this->handleDataTableRequest();
        }

        // Handle request AJAX untuk statistik
        if (request()->ajax() && request()->query('type') === 'statistic') {
            return $this->handleStatisticRequest();
        }

        // Tampilkan view default
        return view('admins.user.index');
    }

    /**
     * Handle DataTables request.
     */
    protected function handleDataTableRequest()
    {
        $data = User::when(request()->query('status') === 'ACTIVE', function ($query) {
            return $query->whereNotNull('last_login');
        })->when(request()->query('status') === 'INACTIVE', function ($query) {
            return $query->whereNull('last_login');
        })->latest();
        return DataTables::of($data)
            ->addColumn('name', function ($data) {
                return $this->generateUserCard($data);
            })
            ->addColumn('status', function ($data) {
                return $data->last_login
                    ? '<span class="badge badge-success">Aktif</span>'
                    : '<span class="badge badge-danger">Tidak Aktif</span>';
            })
            ->editColumn('last_login', function ($data) {
                return $data->last_login
                    ? Carbon::parse($data->last_login)->diffForHumans()
                    : '-';
            })
            ->addColumn('action', function ($data) {
                return $this->generateActionButtons($data);
            })
            ->rawColumns(['action', 'name', 'status'])
            ->make(true);
    }

    /**
     * Handle statistic request.
     */
    protected function handleStatisticRequest()
    {
        $total = User::count();
        $active = User::whereNotNull('last_login')->count();
        $inactive = User::whereNull('last_login')->count();

        return response()->json([
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ]);
    }

    /**
     * Generate HTML for user card.
     */
    protected function generateUserCard($data)
    {
        $userName = $data?->name ?? '-';
        $userPhone = $data?->phone ?? '-';
        $avatarUrl = $data?->avatar ?: asset('assets/media/avatars/default.png');
        $whatsappLink = $this->generateWhatsAppLink($userPhone);

        return '<div class="student-card" style="display: flex; align-items: center; gap: 10px;">
        <img src="' . $avatarUrl . '" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
        <div>
            <div><strong>' . $userName . '</strong></div>
            <div>' .
            ($whatsappLink
                ? '<a href="' . $whatsappLink . '" target="_blank" style="text-decoration: none; color: inherit;">' . $userPhone . '</a>'
                : $userPhone
            ) .
            '</div>
        </div>
    </div>';
    }

    /**
     * Generate WhatsApp link if phone number starts with '0'.
     */
    protected function generateWhatsAppLink($phone)
    {
        if ($phone !== '-' && substr($phone, 0, 1) === '0') {
            return 'https://wa.me/62' . substr($phone, 1);
        }
        return null;
    }

    /**
     * Generate action buttons for DataTables.
     */
    protected function generateActionButtons($data)
    {
        $actionEdit = route('user.edit', $data->id);
        $actionDelete = route('user.destroy', $data->id);

        return "<div class='d-flex justify-content-center'>" .
            view('components.action.edit', ['action' => $actionEdit, 'name' => 'Wali Santri']) . '&nbsp;' .
            view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Wali Santri']) .
            "</div>";
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Wali Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.user.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        if (!Auth::user()->can('Create Wali Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        if ($request->hasFile('avatar')) {
            $data['avatar'] = 'storage/' . $request->file('avatar')->store('images/avatar', ['disk' => 'public']);
        }
        User::create($data);
        return redirect()->route('user.index')->with('success', 'Berhasil menambahkan data user');
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
    public function edit(User $user)
    {
        if (!Auth::user()->can('Edit Wali Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.user.create-edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, User $user)
    {
        if (!Auth::user()->can('Edit Wali Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->except('password');
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }
        if ($request->hasFile('avatar')) {
            $data['avatar'] = 'storage/' . $request->file('avatar')->store('images/avatar', ['disk' => 'public']);
        }
        $user->update($data);
        return redirect()->route('user.index')->with('success', 'Berhasil mengubah data user');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if (!Auth::user()->can('Delete Wali Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        file_exists($user->avatar) ? unlink($user->avatar) : '';
        $user->delete();
        return redirect()->route('user.index')->with('success', 'Berhasil menghapus data user');
    }

    /**
     * Remove the specified resource from storage.
     */

    public function import(Request $request)
    {
        if (!Auth::user()->can('Create Wali Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        try {
            $request->validate([
                'file' => 'required|mimes:xls,xlsx'
            ]);

            return DB::transaction(function () use ($request) {
                Excel::import(new UserImportData, $request->file('file'));
                return redirect()->route('user.index')->with('success', 'Data berhasil diimport');
            });
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Import failed: ' . $e->getMessage());

            // Return with an error message or handle the exception as needed
            return redirect()->back()->with('error', 'Data gagal diimport');
        }
    }
}

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
        if (!Auth::user()->can('Manage Wali Santri')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = User::latest();
            return DataTables::of($data)
                ->addColumn('name', function ($data) {
                    $userName = $data ? $data?->name : '-';
                    $userPhone = $data ? $data?->phone : '-';

                    // Check if avatar exists, if not, use default avatar
                    $avatarUrl = $data?->avatar ? $data?->avatar : asset('assets/media/avatars/default.png');

                    // Check if the phone number starts with '0'
                    $whatsappLink = null;
                    if ($userPhone !== '-' && substr($userPhone, 0, 1) === '0') {
                        // Replace the leading '0' with the country code (e.g., '62' for Indonesia)
                        $formattedPhone = '62' . substr($userPhone, 1);
                        $whatsappLink = 'https://wa.me/' . $formattedPhone;
                    }

                    // Return HTML structure for the card with avatar, name, and class
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
                })
                ->addColumn('status', function ($data) {
                    return $data->last_login ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-danger">Tidak Aktif</span>';
                })
                ->editColumn('last_login', function ($data) {
                    return $data->last_login ? Carbon::parse($data->last_login)->diffForHumans() : '-';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('user.edit', $data->id);
                    $actionDelete = route('user.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Wali Santri']) . '&nbsp;' .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Wali Santri']) .
                        "</div>";
                })
                ->rawColumns(['action', 'name', 'status'])
                ->make(true);
        }
        return view('admins.user.index');
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
            $data['avatar'] = 'storage/' . $request->file('avatar')->store('images/avatar', 'public');
        } else {
            $data['avatar'] = 'assets/media/avatars/default_avatar.jpg';
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
            $data['avatar'] = 'storage/' . $request->file('avatar')->store('images/avatar', 'public');
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

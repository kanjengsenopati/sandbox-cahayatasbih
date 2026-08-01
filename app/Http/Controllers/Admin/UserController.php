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
            return $query->where('status', 'ACTIVE')->whereNotNull('last_login');
        })->when(request()->query('status') === 'INACTIVE', function ($query) {
            return $query->where('status', 'ACTIVE')->whereNull('last_login');
        })->when(request()->query('status') === 'VERIFICATION', function ($query) {
            return $query->where('status', 'VERIFICATION');
        })->when(request()->query('jamaah_status'), function ($query) {
            return $query->where('jamaah_status', request()->query('jamaah_status'));
        })->when(request()->filled('search_name'), function ($query) {
            $search = strtolower(trim(request()->query('search_name')));
            return $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(phone) LIKE ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $search . '%']);
            });
        })->latest();
        return DataTables::of($data)
            ->filterColumn('name', function($query, $keyword) {
                $search = strtolower(trim($keyword));
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                      ->orWhereRaw('LOWER(phone) LIKE ?', ['%' . $search . '%'])
                      ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $search . '%']);
                });
            })
            ->addColumn('name', function ($data) {
                return $this->generateUserCard($data);
            })
            ->addColumn('status', function ($data) {
                if ($data->status === 'VERIFICATION') {
                    return '<span class="badge badge-warning">Butuh Verifikasi</span>';
                }
                return $data->last_login
                    ? '<span class="badge badge-success">Aktif</span>'
                    : '<span class="badge badge-danger">Tidak Aktif</span>';
            })
            ->addColumn('jamaah_status', function ($data) {
                if ($data->jamaah_status === 'JAMAAH') {
                    return '<span class="badge badge-light-success fw-bolder px-2 py-1">Jamaah</span>';
                } elseif ($data->jamaah_status === 'NON_JAMAAH') {
                    return '<span class="badge badge-light-danger fw-bolder px-2 py-1">Non Jamaah</span>';
                } elseif ($data->jamaah_status === 'MUKIMIN') {
                    return '<span class="badge badge-light-primary fw-bolder px-2 py-1">Mukimin</span>';
                }
                return '<span class="badge badge-light-danger fw-bolder px-2 py-1">Non Jamaah</span>';
            })
            ->editColumn('last_login', function ($data) {
                return $data->last_login
                    ? Carbon::parse($data->last_login)->diffForHumans()
                    : '-';
            })
            ->addColumn('action', function ($data) {
                return $this->generateActionButtons($data);
            })
            ->rawColumns(['action', 'name', 'status', 'jamaah_status'])
            ->make(true);
    }

    /**
     * Handle statistic request.
     */
    protected function handleStatisticRequest()
    {
        $total = User::count();
        $active = User::where('status', 'ACTIVE')->whereNotNull('last_login')->count();
        $inactive = User::where('status', 'ACTIVE')->whereNull('last_login')->count();
        $verification = User::where('status', 'VERIFICATION')->count();

        return response()->json([
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'verification' => $verification,
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
        $actionReset = route('user.reset-password', $data->id);

        $buttons = "<div class='d-flex justify-content-center align-items-center gap-1'>";

        if ($data->status === 'VERIFICATION') {
            $actionVerify = route('user.verify', $data->id);
            $buttons .= "<button type='button' class='btn btn-icon btn-sm btn-light-success btn-verify me-1' data-url='{$actionVerify}' title='Verifikasi Wali Santri'><i class='fa fa-check fs-6'></i></button>";
        }

        $buttons .= "<button type='button' class='btn btn-icon btn-sm btn-light-warning btn-reset-password me-1' data-url='{$actionReset}' data-name='{$data->name}' title='Reset Password'><i class='fa fa-key fs-6 text-warning'></i></button>";

        $buttons .= view('components.action.edit', ['action' => $actionEdit, 'name' => 'Wali Santri']) . '&nbsp;' .
            view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Wali Santri']) .
            "</div>";

        return $buttons;
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

        // Check for double entry using name similarity and phone number
        $duplicate = User::checkDoubleEntry($data['name'], $data['phone']);
        if ($duplicate) {
            $data['status'] = 'VERIFICATION';
            User::create($data);
            return redirect()->route('user.index')->with('warning', 'Data Wali Santri terdeteksi ganda dengan data sebelumnya. Status diatur ke "Butuh Verifikasi".');
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

        // Check for double entry (excluding the current user being updated)
        $duplicate = User::checkDoubleEntry($data['name'], $data['phone']);
        $oldJamaahStatus = $user->jamaah_status;
        
        if ($duplicate && $duplicate->id !== $user->id) {
            $data['status'] = 'VERIFICATION';
            $user->update($data);
            
            if (isset($data['jamaah_status']) && $data['jamaah_status'] !== $oldJamaahStatus) {
                \App\Services\PpdbFeeSyncService::syncFeeForUser($user);
            }
            
            return redirect()->route('user.index')->with('warning', 'Data Wali Santri terdeteksi ganda dengan data sebelumnya. Status diatur ke "Butuh Verifikasi".');
        }

        $user->update($data);
        
        if (isset($data['jamaah_status']) && $data['jamaah_status'] !== $oldJamaahStatus) {
            \App\Services\PpdbFeeSyncService::syncFeeForUser($user);
        }
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
     * Update multiple Wali Santri status in bulk.
     */
    public function bulkUpdateStatus(Request $request)
    {
        if (!Auth::user()->can('Edit Wali Santri')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Maaf, Anda tidak memiliki akses untuk mengubah data Wali Santri.'
            ], 403);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|exists:users,id',
            'jamaah_status' => 'required|in:JAMAAH,NON_JAMAAH,MUKIMIN',
        ]);

        try {
            DB::beginTransaction();

            $updatedCount = User::whereIn('id', $request->ids)
                ->update(['jamaah_status' => $request->jamaah_status]);

            // Sync PPDB fees for the updated users
            $users = User::whereIn('id', $request->ids)->get();
            foreach ($users as $user) {
                \App\Services\PpdbFeeSyncService::syncFeeForUser($user);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil memperbarui status keanggotaan untuk {$updatedCount} Wali Santri.",
                'count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk status update failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memproses pembaruan massal.'
            ], 500);
        }
    }

    /**
     * Check if manual input name/phone matches duplicates.
     */
    public function checkDuplicate(Request $request)
    {
        $name = trim($request->name);
        $phone = trim($request->phone);
        $userId = $request->id;

        // format phone number if needed
        if (substr($phone, 0, 1) !== '0' && strlen($phone) > 1) {
            $phone = '0' . $phone;
        }
        if (substr($phone, 0, 2) == '62') {
            $phone = '0' . substr($phone, 2);
        }

        $query = User::query();
        if ($userId) {
            $query->where('id', '<>', $userId);
        }

        // We check for exact duplicate of phone or name + phone
        $duplicate = $query->where(function ($q) use ($name, $phone) {
            $q->where('phone', $phone)
              ->orWhere(function ($subQ) use ($name, $phone) {
                  $subQ->where('name', 'like', '%' . $name . '%')
                       ->where('phone', $phone);
              });
        })->first();

        if ($duplicate) {
            return response()->json([
                'duplicate' => true,
                'message' => "Nama / No WA ini sudah terdaftar atas nama: {$duplicate->name} ({$duplicate->phone})",
                'user' => [
                    'name' => $duplicate->name,
                    'phone' => $duplicate->phone,
                ]
            ]);
        }

        return response()->json(['duplicate' => false]);
    }

    /**
     * Verify / Approve a Wali Santri who is in VERIFICATION status.
     */
    public function verify(User $user)
    {
        if (!Auth::user()->can('Edit Wali Santri')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Maaf, Anda tidak memiliki akses untuk memverifikasi Wali Santri.'
            ], 403);
        }

        try {
            $user->update(['status' => 'ACTIVE']);
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil memverifikasi Wali Santri.'
            ]);
        } catch (\Exception $e) {
            Log::error('Verification failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memverify data.'
            ], 500);
        }
    }

    /**
     * Reset password of a Wali Santri to 12345678.
     */
    public function resetPassword(User $user)
    {
        if (!Auth::user()->can('Edit Wali Santri')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Maaf, Anda tidak memiliki akses untuk reset password Wali Santri.'
            ], 403);
        }

        try {
            $user->update([
                'password' => bcrypt('12345678')
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil Reset Password 12345678',
                'user_name' => $user->name
            ]);
        } catch (\Exception $e) {
            Log::error('Reset password failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mereset password.'
            ], 500);
        }
    }

    public function bulkDestroy(Request $request)
    {
        if (!Auth::user()->can('Delete Wali Santri')) {
            return response()->json(['success' => false, 'message' => 'Maaf, Anda tidak memiliki akses untuk menghapus data wali santri'], 403);
        }

        $ids = $request->input('ids');
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data wali santri yang dipilih'], 400);
        }

        try {
            DB::transaction(function () use ($ids) {
                $users = User::whereIn('id', $ids)->get();
                foreach ($users as $user) {
                    if ($user->avatar && file_exists($user->avatar)) {
                        unlink($user->avatar);
                    }
                    $user->delete();
                }
            });

            return response()->json(['success' => true, 'message' => 'Berhasil menghapus data wali santri terpilih']);
        } catch (\Exception $e) {
            Log::error('Bulk delete users failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data wali santri terpilih: ' . $e->getMessage()], 500);
        }
    }
}

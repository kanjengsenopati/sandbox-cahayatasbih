<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends BaseWaliApiController
{
    public function show()
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            $isDualRole = \App\Models\User::whereIn('phone', $this->getPhoneVariations($user->phone))->exists();
            return response()->json([
                'user' => $user,
                'role' => 'asatidz',
                'is_dual_role' => $isDualRole,
                'students' => []
            ]);
        }

        $user = Auth::guard('wali')->user();
        $students = Student::where('user_id', $user->id)->get();
        $isDualRole = \App\Models\Admin::whereIn('phone', $this->getPhoneVariations($user->phone))->exists();
        
        return response()->json([
            'user' => $user,
            'role' => 'wali',
            'is_dual_role' => $isDualRole,
            'students' => $students
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::guard('wali')->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'avatar' => 'nullable|image|max:1024',
        ]);

        $data = $request->only(['name', 'email', 'phone']);
        
        if ($request->hasFile('avatar')) {
            $data['avatar'] = 'storage/' . $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);
        
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    public function switchRole(Request $request)
    {
        if (Auth::guard('wali')->check()) {
            $user = Auth::guard('wali')->user();
            $admin = \App\Models\Admin::whereIn('phone', $this->getPhoneVariations($user->phone))->first();
            if ($admin && $admin->is_active) {
                // Check if Admin has PWA Perizinan access permission
                if (!$admin->hasAnyPermission(['Manage Perizinan', 'Approve Perizinan', 'Scan Perizinan'])) {
                    return response()->json(['message' => 'Maaf, Anda tidak memiliki hak akses PWA Perizinan.'], 403);
                }

                Auth::guard('wali')->logout();
                Auth::guard('web')->login($admin);
                return response()->json([
                    'message' => 'Switched to Asatidz successfully',
                    'role' => 'asatidz'
                ]);
            }
        } elseif (Auth::guard('web')->check()) {
            $admin = Auth::guard('web')->user();
            $user = \App\Models\User::whereIn('phone', $this->getPhoneVariations($admin->phone))->first();
            if ($user && $user->is_active) {
                Auth::guard('web')->logout();
                Auth::guard('wali')->login($user);
                return response()->json([
                    'message' => 'Switched to Wali successfully',
                    'role' => 'wali'
                ]);
            }
        }

        return response()->json(['message' => 'Switch role failed'], 400);
    }

    protected function getPhoneVariations($phone)
    {
        $digits = preg_replace('/\D/', '', $phone);
        $variations = [$phone, $digits];

        if (str_starts_with($digits, '62')) {
            $local = '0' . substr($digits, 2);
            $variations[] = $local;
            $variations[] = '+' . $digits;
        } elseif (str_starts_with($digits, '0')) {
            $intl = '62' . substr($digits, 1);
            $variations[] = $intl;
            $variations[] = '+' . $intl;
        }

        return array_unique($variations);
    }
}

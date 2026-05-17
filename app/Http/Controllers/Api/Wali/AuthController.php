<?php

namespace App\Http\Controllers\Api\Wali;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => 'required',
            'password' => 'required',
            'role' => 'nullable|string|in:wali,asatidz',
        ]);

        $phone = $credentials['phone'];
        $password = $credentials['password'];
        $role = $request->input('role');

        // Pre-Check Dual Role Identity
        if (!$role) {
            $waliUser = \App\Models\User::where('phone', $phone)->first();
            $asatidzUser = \App\Models\Admin::where('phone', $phone)->first();

            $isWaliValid = $waliUser && \Illuminate\Support\Facades\Hash::check($password, $waliUser->password);
            $isAsatidzValid = $asatidzUser && \Illuminate\Support\Facades\Hash::check($password, $asatidzUser->password);

            if ($isWaliValid && $isAsatidzValid) {
                return response()->json([
                    'status' => 'requires_role_selection',
                    'message' => 'Identitas ganda terdeteksi. Silakan pilih peran masuk Anda.',
                    'roles' => ['wali', 'asatidz']
                ]);
            }
        }

        // 1. Attempt Wali Santri Auth
        if (!$role || $role === 'wali') {
            if (Auth::guard('wali')->attempt(['phone' => $phone, 'password' => $password])) {
                $user = Auth::guard('wali')->user();
                if ($user->is_active) {
                    return response()->json([
                        'message' => 'Login successful',
                        'role' => 'wali',
                        'user' => $user
                    ]);
                } else {
                    Auth::guard('wali')->logout();
                    return response()->json(['message' => 'Account inactive'], 403);
                }
            }
        }

        // 2. Attempt Asatidz Auth (Admin model)
        if (!$role || $role === 'asatidz') {
            if (Auth::guard('web')->attempt(['phone' => $phone, 'password' => $password])) {
                $admin = Auth::guard('web')->user();
                if ($admin->is_active) {
                    // Check if Admin has PWA Perizinan access permission
                    if (!$admin->hasAnyPermission(['Manage Perizinan', 'Approve Perizinan', 'Scan Perizinan'])) {
                        Auth::guard('web')->logout();
                        return response()->json(['message' => 'Maaf, Anda tidak memiliki hak akses PWA Perizinan.'], 403);
                    }

                    return response()->json([
                        'message' => 'Login successful',
                        'role' => 'asatidz',
                        'user' => $admin
                    ]);
                } else {
                    Auth::guard('web')->logout();
                    return response()->json(['message' => 'Account inactive'], 403);
                }
            }
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function logout()
    {
        if (Auth::guard('wali')->check()) {
            Auth::guard('wali')->logout();
        }
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }
        return response()->json(['message' => 'Logout successful']);
    }
}

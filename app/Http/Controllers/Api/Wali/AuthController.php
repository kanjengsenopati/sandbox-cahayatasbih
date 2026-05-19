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

        $variations = $this->getPhoneVariations($phone);

        // Pre-Check Dual Role Identity
        if (!$role) {
            $waliUser = \App\Models\User::whereIn('phone', $variations)->first();
            $asatidzUser = \App\Models\Admin::whereIn('phone', $variations)->first();

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
            $waliUser = \App\Models\User::whereIn('phone', $variations)->first();
            if ($waliUser) {
                if (Auth::guard('wali')->attempt(['phone' => $waliUser->phone, 'password' => $password])) {
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
        }

        // 2. Attempt Asatidz Auth (Admin model)
        if (!$role || $role === 'asatidz') {
            $asatidzUser = \App\Models\Admin::whereIn('phone', $variations)->first();
            if ($asatidzUser) {
                if (Auth::guard('web')->attempt(['phone' => $asatidzUser->phone, 'password' => $password])) {
                    $admin = Auth::guard('web')->user();
                    if ($admin->is_active) {
                        // Check if Admin is allowed to access PWA
                        if ($admin->access_scope === 'backoffice') {
                            Auth::guard('web')->logout();
                            return response()->json(['message' => 'Maaf, akun Anda tidak memiliki hak akses PWA.'], 403);
                        }

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
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
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

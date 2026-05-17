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
        ]);

        $phone = $credentials['phone'];
        $password = $credentials['password'];

        // 1. Attempt Wali Santri Auth
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

        // 2. Attempt Asatidz Auth (Admin model)
        if (Auth::guard('web')->attempt(['phone' => $phone, 'password' => $password])) {
            $admin = Auth::guard('web')->user();
            if ($admin->is_active) {
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

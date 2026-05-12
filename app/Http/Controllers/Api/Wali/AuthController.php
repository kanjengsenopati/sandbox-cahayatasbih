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

        if (Auth::guard('wali')->attempt($credentials)) {
            $user = Auth::guard('wali')->user();
            if ($user->is_active) {
                return response()->json([
                    'message' => 'Login successful',
                    'user' => $user
                ]);
            } else {
                Auth::guard('wali')->logout();
                return response()->json(['message' => 'Account inactive'], 403);
            }
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function logout()
    {
        Auth::guard('wali')->logout();
        return response()->json(['message' => 'Logout successful']);
    }
}

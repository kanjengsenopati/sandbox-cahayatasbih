<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CtMobileAuthController extends Controller
{
    public function index()
    {
        return view('users.auth.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        $phone = $request->input('phone');
        $password = $request->input('password');
        $remember = $request->has('remember');

        // 1. Attempt Wali Santri Auth (User model)
        if (Auth::guard('wali')->attempt(['phone' => $phone, 'password' => $password], $remember)) {
            $user = Auth::guard('wali')->user();
            if ($user->is_active) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'role' => 'wali',
                        'redirect' => route('wali.dashboard')
                    ]);
                }
                return redirect()->route('wali.dashboard');
            } else {
                Auth::guard('wali')->logout();
                return back()->with(['warning' => 'Maaf akun tidak aktif / diblokir, silakan hubungi administrator !!'])->withInput($request->only('phone'));
            }
        }

        // 2. Attempt Asatidz Auth (Admin model)
        if (Auth::guard('web')->attempt(['phone' => $phone, 'password' => $password], $remember)) {
            $admin = Auth::guard('web')->user();
            if ($admin->is_active) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'role' => 'asatidz',
                        'redirect' => '/ct-mobile/app#/asatidz/dashboard'
                    ]);
                }
                return redirect('/ct-mobile/app#/asatidz/dashboard');
            } else {
                Auth::guard('web')->logout();
                return back()->with(['warning' => 'Maaf akun Anda dinonaktifkan, silakan hubungi administrator !!'])->withInput($request->only('phone'));
            }
        }

        return redirect()->back()
            ->with('error', 'Nomor WhatsApp atau password salah')
            ->withInput($request->only('phone', 'remember'));
    }

    public function logout()
    {
        if (Auth::guard('wali')->check()) {
            Auth::guard('wali')->logout();
        }
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }
        return redirect('/ct-mobile/login');
    }

    public function register()
    {
        return view('users.auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $userData = [
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'avatar' => 'assets/media/avatars/default.png',
        ];

        try {
            DB::transaction(function () use ($userData) {
                User::create($userData);
            });

            return redirect()->route('wali.login')->with('success', 'Registrasi berhasil, silakan login');
        } catch (\Exception $e) {
            Log::error('Error during user registration: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat registrasi, silakan coba lagi.');
        }
    }
}

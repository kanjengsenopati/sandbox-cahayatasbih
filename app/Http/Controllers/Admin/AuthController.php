<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\AuthRequest;

class AuthController extends Controller
{
    public function index()
    {
        return view('admins.auth.login');
    }

    public function authenticate(AuthRequest $request)
    {
        if (Auth::guard('web')->attempt($request->validated(), $request->remember)) {
            $user = Auth::guard('web')->user();
            if ($user->is_active) {
                if ($user->access_scope === 'pwa') {
                    Auth::guard('web')->logout();
                    return back()->with(['warning' => 'Maaf, akun Anda dikonfigurasi hanya untuk akses PWA !!'])->withInput($request->only('email'));
                }
                $user->update([
                    'last_login_at' => now(),
                ]);
                return  redirect()->intended('dashboard');
            } else {
                Auth::guard('web')->logout();
                return back()->with(['warning' => 'Maaf akun tidak aktif / diblokir, silakan hubungi administrator !!'])->withInput($request->only('email'));
            }
        } else {
            return back()->with(['warning' => 'Maaf email atau password tidak sesuai'])->withInput($request->only('email'));
        }
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        return  redirect('/');
    }
}

<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\User\AuthRequest;
use App\Http\Requests\User\WaliRegisterRequest;

class WaliAuthController extends Controller
{
    public function index()
    {
        return view('users.auth.login');
    }

    public function authenticate(AuthRequest $request)
    {
        if (Auth::guard('wali')->attempt($request->validated(), $request->remember)) {
            if (Auth::guard('wali')->user()->is_active) {
                return redirect()->intended('/wali/dashboard');
            } else {
                Auth::guard('wali')->logout();
                return back()->with(['warning' => 'Maaf akun tidak aktif / diblokir, silakan hubungi administrator !!'])->withInput($request->only('email'));
            }
        } else {
            return back()->with(['warning' => 'Maaf Nomor atau password tidak sesuai'])
                ->withInput($request->only('phone'));
        }
    }

    public function logout()
    {
        Auth::guard('wali')->logout();
        return redirect('/wali/login');
    }

    public function register()
    {
        return view('users.auth.register');
    }

    public function store(WaliRegisterRequest $request)
    {
        $userData = $request->validated();

        // Hash the password
        $userData['password'] = bcrypt($userData['password']);

        // Set a default avatar
        $userData['avatar'] = 'assets/media/avatars/default_avatar.jpg';

        // Create the user
        User::create($userData);

        // Redirect with success message
        return redirect()->route('wali.login')->with('success', 'Registrasi berhasil, silakan login');
    }
}

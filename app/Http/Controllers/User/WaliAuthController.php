<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
                return redirect()->route('wali.dashboard');
            } else {
                Auth::guard('wali')->logout();
                return back()->with(['warning' => 'Maaf akun tidak aktif / diblokir, silakan hubungi administrator !!'])->withInput($request->only('email'));
            }
        } else {
            return redirect()->back()->with('error', 'Nomor Hp atau password salah')
                ->withInput($request->only('phone', 'remember'));
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
        $userData['password'] = Hash::make($userData['password']); // Use Hash facade for better readability

        // Set a default avatar
        $userData['avatar'] = 'assets/media/avatars/default.png';

        try {
            // Use DB transaction to ensure data integrity
            DB::transaction(function () use ($userData) {
                // Create the user
                User::create($userData);
            });

            // Redirect with success message
            return redirect()->route('wali.login')->with('success', 'Registrasi berhasil, silakan login');
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error during user registration: ' . $e->getMessage());

            // Redirect with error message
            return redirect()->back()->with('error', 'Terjadi kesalahan saat registrasi, silakan coba lagi.');
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Wali;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends BaseWaliApiController
{
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password:wali',
            'new_password' => 'required|min:8|confirmed',
        ]);

        Auth::guard('wali')->user()->update([
            'password' => Hash::make($request->new_password)
        ]);
        
        return response()->json(['message' => 'Password updated successfully']);
    }
}

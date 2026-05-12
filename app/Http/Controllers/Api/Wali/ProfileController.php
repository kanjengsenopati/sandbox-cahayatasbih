<?php

namespace App\Http\Controllers\Api\Wali;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends BaseWaliApiController
{
    public function show()
    {
        $user = Auth::guard('wali')->user();
        $students = Student::where('user_id', $user->id)->get();
        
        return response()->json([
            'user' => $user,
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
}

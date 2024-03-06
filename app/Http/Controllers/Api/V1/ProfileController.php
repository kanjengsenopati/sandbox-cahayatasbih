<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ChangePasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Api\V1\UpdateProfileRequest;

class ProfileController extends Controller
{

    public function index()
    {
        $user = User::with('student')->findOrFail(Auth::id());
        return $this->getSuccessResponse($user);
    }

    public function updateAvatar()
    {
        $user = User::findOrFail(Auth::id());
        file_exists($user->avatar) && unlink($user->avatar);
        $user->update([
            'avatar' => 'storage/' . request()->file('avatar')->store('avatars', 'public')
        ]);
        return $this->postSuccessResponse("Berhasil mengubah avatar", $user);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = User::findOrFail(Auth::id());
        $user->update($request->validated());

        return $this->postSuccessResponse("Berhasil mengubah profil", $user);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = User::findOrFail(Auth::id());
        // check apakah password lama benar
        if (!Hash::check($request->old_password, $user->password)) {
            return $this->failedResponse("Maaf, Password lama anda salah");
        }
        $user->update([
            'password' => bcrypt($request->password)
        ]);

        return $this->postSuccessResponse("Berhasil mengubah password", $user);
    }
}

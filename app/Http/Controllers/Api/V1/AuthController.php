<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\CheckEmailRequest;
use App\Http\Requests\Api\V1\CheckPhoneRequest;

class AuthController extends Controller
{
    protected function handleLogin(Request $request, $field, $errorText)
    {
        // Validate the request
        $validatedData = $request->validate([
            $field => 'required',
            'password' => 'required'
        ]);

        // Retrieve the user based on the given field
        $user = User::where($field, $validatedData[$field])->first();

        // Check if the user exists
        if (!$user) {
            return $this->failedResponse($errorText);
        }

        // Check if the provided password is correct
        if (!Hash::check($validatedData['password'], $user->password)) {
            return $this->failedResponse('Password salah');
        }

        // Check if the user is active
        if (!$user->is_active) {
            return $this->failedResponse('Maaf Nih, akun kamu sedang Diblokir. Hubungi admin buat info lebih lanjut ya');
        }

        // Update FCM token if provided
        if ($request->has('fcm_token')) {
            $this->update_fcm_token($request->fcm_token, $user->id);
        }

        // Generate access token
        $accessToken = $user->createToken('cahaya-tasbih')->accessToken;

        // Return success response
        return $this->postSuccessResponse('Selamat anda berhasil login !', [
            'user'  => $user->load('student'),
            'token' => $accessToken,
        ]);
    }

    public function phoneLogin(Request $request)
    {
        return $this->handleLogin($request, 'phone', 'Nomor telepon belum terdaftar. Silahkan daftar terlebih dahulu');
    }



    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $user->update(['fcm_token' => null]);
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return $this->postSuccessResponse('Logout berhasil!');
        } else {
            return $this->failedResponse('Anda telah logout sebelumnya');
        }
    }


    public function update_fcm_token($fcm_token, $user_id)
    {
        if ($fcm_token) {
            $dataToUpdate = [];

            if ($fcm_token) {
                $dataToUpdate['fcm_token'] = $fcm_token;
            }
            User::find($user_id)->update($dataToUpdate);
        }
    }

    public function updateFcm()
    {
        $this->update_fcm_token(request('fcm_token'), Auth::id());
        return $this->postSuccessResponse('Fcm Token berhasil diupdate');
    }


    public function closedAccount(Request $request)
    {
        $request->user()->update(['is_active' => false]);
        $request->user()->token()->revoke();
        return $this->postSuccessResponse('Akun anda telah dinonaktifkan.
        Silahkan hubungi administrator untuk informasi lebih lanjut.', $request->user());
    }

    public function updateFcmToken(Request $request)
    {
        $this->update_fcm_token($request->fcm_token, Auth::user()->firebase_uid, Auth::id());
        return $this->postSuccessResponse('Fcm Token berhasil diupdate');
    }
}

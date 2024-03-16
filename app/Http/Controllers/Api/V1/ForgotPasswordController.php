<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Jobs\SendToWhatsappNotificationJob;

class ForgotPasswordController extends Controller
{
    public function requestToken(Request $request)
    {
        // 0. Validasi input
        $this->validate($request, [
            'phone' => 'required|numeric|exists:users,phone'
        ]);
        // 1. Generate token
        $token = $this->generateToken();
        // 2. Simpan token dan nomor telepon ke dalam password_reset_tokens table
        $this->saveToken($request, $token);
        // 3. Kirim token ke nomor telepon user
        $this->sendToken($request, $token);
        return $this->postSuccessResponse("Kode berhasil dikirim", null);
    }

    private function generateToken()
    {
        return Str::random(6);
    }

    private function saveToken($request, $token)
    {
        $tokenData = DB::table('password_reset_tokens')->where('phone', $request->phone)->first();

        // Lakukan operasi update atau insert dengan menggunakan method ternary
        return $tokenData ?
            DB::table('password_reset_tokens')
            ->where('phone', $request->phone)
            ->update(['token' => $token, 'created_at' => now()]) :
            DB::table('password_reset_tokens')->insert([
                'phone' => $request->phone,
                'token' => $token, 'created_at' => now()
            ]);
    }


    private function sendToken($request, $token)
    {
        // Kirim pesan WhatsApp menggunakan job yang di-dispatch
        $message = "🔒 *Reset Password*\n" .
            "Assalamualaikum, Anda telah meminta reset kata sandi. " .
            "Berikut adalah kode reset kata sandi Anda: *{$token}*. " .
            "Silakan masukkan kode ini untuk melanjutkan proses reset kata sandi.";

        // Tambahkan footer
        $footer = "\n\n*Terima kasih,*\n*PPTQ Cahaya Tasbih*";

        // Gabungkan pesan dengan footer
        $fullMessage = $message . $footer;

        dispatch(new SendToWhatsappNotificationJob($request->phone, $fullMessage));
    }


    public function matchToken(Request $request)
    {
        // 0. Validasi input
        $this->validate($request, [
            'phone' => 'required|numeric|exists:users,phone',
            'token' => 'required',
        ]);
        // 1. Cek apakah token cocok dengan nomor telepon
        $tokenData = DB::table('password_reset_tokens')
            ->where('phone', $request->phone)
            ->where('token', $request->token)
            ->first();
        if (!$tokenData) {
            return $this->failedResponse("Kode Yang Anda Masukkan Salah");
        }
        // 2. Jika cocok, kembalikan response
        return $this->postSuccessResponse("Kode Yang Anda Masukkan Benar", $tokenData);
    }

    public function resetPassword(Request $request)
    {
        // 0. Validasi input
        $this->validate($request, [
            'phone' => 'required|numeric|exists:users,phone',
            'token' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);
        // 1. Ambil data token
        $tokenData = DB::table('password_reset_tokens')
            ->where('phone', $request->phone)
            ->where('token', $request->token)
            ->first();
        // 2. Jika token tidak ditemukan, kembalikan response
        if (!$tokenData) {
            return $this->failedResponse("Token tidak ditemukan");
        }
        // 3. Jika token ditemukan, update password user
        $user = User::where('phone', $request->phone)->first();
        $user->update(['password' => bcrypt($request->password)]);
        // 4. Hapus token dari password_reset_tokens table
        DB::table('password_reset_tokens')
            ->where('phone', $request->phone)
            ->delete();
        // 5. Kembalikan response
        return $this->postSuccessResponse("Kata sandi berhasil diubah", $user);
    }
}

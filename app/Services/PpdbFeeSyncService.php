<?php

namespace App\Services;

use App\Models\User;
use App\Models\PpdbRegistration;
use App\Models\PpdbTrack;
use App\Models\Transaction;
use App\Models\ApplicationSetting;
use Illuminate\Support\Facades\Log;

class PpdbFeeSyncService
{
    /**
     * Synchronize PPDB Registration fee and track based on parent's jamaah_status.
     * 
     * @param User $user
     * @return void
     */
    public static function syncFeeForUser(User $user)
    {
        // Temukan semua pendaftaran PPDB yang masih belum dibayar (UNPAID) 
        // dan berstatus PENDING (belum diverifikasi admin dan masih terbuka).
        $registrations = PpdbRegistration::where('user_id', $user->id)
            ->where('payment_status', 'UNPAID')
            ->whereIn('status', [PpdbRegistration::STATUS_PENDING, PpdbRegistration::STATUS_KTA_REVISION])
            ->get();

        // Tentukan target tipe registrasi berdasarkan jamaah_status wali
        $targetRegistrationType = $user->jamaah_status === 'JAMAAH' 
            ? PpdbTrack::TYPE_JAMAAH 
            : PpdbTrack::TYPE_UMUM;

        $appSetting = ApplicationSetting::latest()->first();

        foreach ($registrations as $registration) {
            $currentTrack = $registration->track;
            
            // Jika tidak ada track atau track saat ini sudah sesuai dengan status, skip
            if (!$currentTrack || $currentTrack->registration_type === $targetRegistrationType) {
                continue;
            }

            // Cari track tujuan yang memiliki gelombang dan sekolah yang sama
            $targetTrack = PpdbTrack::where('ppdb_wave_id', $currentTrack->ppdb_wave_id)
                ->where('school_id', $currentTrack->school_id)
                ->where('registration_type', $targetRegistrationType)
                ->first();

            if (!$targetTrack) {
                Log::warning("Target track PPDB tidak ditemukan untuk Sinkronisasi Fee Wali {$user->name} ({$user->id}) dengan target tipe {$targetRegistrationType}.");
                continue;
            }

            // Update pendaftaran ke track yang baru
            $isMember = $targetRegistrationType === PpdbTrack::TYPE_JAMAAH;
            
            $registration->update([
                'ppdb_track_id' => $targetTrack->id,
                'is_mdti_member' => $isMember, // Asumsikan is_mdti_member berkaitan dengan keanggotaan jamaah
            ]);

            // Ambil transaksi yang belum dibayar terkait pendaftaran ini
            $transaction = Transaction::whereHas('transactionDetails', function($q) use ($registration) {
                $q->where('ppdb_registration_id', $registration->id);
            })->where('status', Transaction::STATUS_PENDING)->first();

            if ($transaction) {
                // Kalkulasi ulang fee berdasarkan track baru
                $paymentFee = $appSetting ? $appSetting->payment_fee : 0;
                $billFee = $appSetting ? $appSetting->bill_fee : 0;
                
                $newPayAmount = $targetTrack->registration_fee + $paymentFee + $billFee;

                $transaction->update([
                    'pay_amount' => $newPayAmount,
                    'app_fee' => $billFee
                ]);
                
                Log::info("PPDB Fee Auto-Update: Berhasil mengupdate transaksi tagihan PPDB Wali {$user->name} menjadi Rp. {$newPayAmount} karena perubahan status jamaah.");
            }
        }

        // Sinkronisasi tagihan (Bills) untuk semua murid terkait user ini,
        // apabila user ini telah menjadi Jamaah/Non Jamaah, tagihan lain 
        // seperti pendaftaran yang digenerate sebagai Bill juga harus diupdate.
        \Illuminate\Support\Facades\Artisan::call('bills:sync-rate', [
            '--user-id' => $user->id,
            '--force' => true
        ]);
    }
}

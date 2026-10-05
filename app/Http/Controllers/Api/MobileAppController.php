<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MobileAppController extends Controller
{
    /**
     * Endpoint pengecekan versi APK CT-Mobile.
     * Mengembalikan informasi versi terbaru dan link download dinamis
     * sesuai host/domain aktif (staging: sim.cahayatasbih.or.id vs main: aplikasi.cahayatasbih.or.id).
     */
    public function versionCheck(Request $request)
    {
        // Default konfigurasi versi rilis APK
        $latestVersionCode = (int) config('app.mobile_app.version_code', 1);
        $latestVersionName = config('app.mobile_app.version_name', '1.0.0');
        $releaseNotes = config('app.mobile_app.release_notes', 'Rilis perdana aplikasi CT-Mobile Android.');
        $forceUpdate = (bool) config('app.mobile_app.force_update', false);

        // Cari ukuran file APK jika ada di storage/public
        $apkPath = public_path('download/ct-mobile-latest.apk');
        $fileSize = '4.5 MB';
        if (File::exists($apkPath)) {
            $bytes = File::size($apkPath);
            $fileSize = round($bytes / 1048576, 2) . ' MB';
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'latest_version_code' => $latestVersionCode,
                'latest_version_name' => $latestVersionName,
                // Menggunakan helper url() agar dinamis terhadap domain staging atau main
                'download_url' => url('/download/ct-mobile-latest.apk'),
                'file_size' => $fileSize,
                'release_notes' => $releaseNotes,
                'force_update' => $forceUpdate,
                'server_time' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Endpoint untuk mendownload file APK CT-Mobile terbaru.
     */
    public function downloadLatestApk()
    {
        $locations = [
            public_path('download/ct-mobile-latest.apk'),
            storage_path('app/public/apk/ct-mobile-latest.apk'),
            public_path('download/ct-mobile.apk'),
        ];

        foreach ($locations as $path) {
            if (File::exists($path)) {
                return response()->download($path, 'ct-mobile-latest.apk', [
                    'Content-Type' => 'application/vnd.android.package-archive',
                    'Cache-Control' => 'no-cache, must-revalidate',
                    'Content-Disposition' => 'attachment; filename="ct-mobile-latest.apk"'
                ]);
            }
        }

        // Jika file belum di-upload ke server, beri pesan informatif
        return response()->json([
            'status' => 'error',
            'message' => 'Berkas APK CT-Mobile belum tersedia di server. Silakan hubungi admin atau periksa kembali nanti.',
            'expected_path' => 'public/download/ct-mobile-latest.apk'
        ], 404);
    }
}

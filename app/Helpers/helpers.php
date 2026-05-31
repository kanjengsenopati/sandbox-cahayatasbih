<?php

if (!function_exists('storage_asset')) {
    /**
     * Generate URL untuk file storage.
     * Jika MASTER_APP_URL diset (mode clone/replica), redirect ke master server.
     * Path assets statis (template bawaan) selalu dari domain sendiri.
     *
     * @param string $path Path relatif file (contoh: "storage/images/avatar/xxx.jpg" atau "proofs/xxx.jpg")
     * @return string Full URL ke file
     */
    function storage_asset(string $path): string
    {
        $path = ltrim($path, '/');
        $masterUrl = config('app.master_url');

        // Path assets statis → selalu dari domain sendiri
        if (str_starts_with($path, 'assets/')) {
            return asset($path);
        }

        // Tentukan base URL: master (jika clone) atau domain sendiri
        if ($masterUrl) {
            // Pastikan path dimulai dengan 'storage/' untuk URL publik
            if (!str_starts_with($path, 'storage/')) {
                $path = 'storage/' . $path;
            }
            return rtrim($masterUrl, '/') . '/' . $path;
        }

        // Default: gunakan asset() biasa (mode master/standalone)
        if (!str_starts_with($path, 'storage/')) {
            $path = 'storage/' . $path;
        }
        return asset($path);
    }
}

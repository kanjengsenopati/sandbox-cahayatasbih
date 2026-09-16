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

        // Jika sudah berupa URL absolut
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            // Jika ini URL lokal (localhost/127.0.0.1) dan file lokal tidak ada di disk,
            // ekstrak path relatifnya agar bisa dialihkan ke master server
            $appUrl = config('app.url');
            $parsedUrl = parse_url($path);
            $isLocalHost = in_array($parsedUrl['host'] ?? '', ['localhost', '127.0.0.1'])
                || ($appUrl && str_contains($path, rtrim($appUrl, '/')));

            if ($isLocalHost && isset($parsedUrl['path'])) {
                $relPath = ltrim($parsedUrl['path'], '/');
                if (!file_exists(public_path($relPath))) {
                    $masterUrl = config('app.master_url');
                    if ($masterUrl) {
                        return rtrim($masterUrl, '/') . '/' . $relPath;
                    }
                }
            }
            return $path;
        }

        // Path assets statis → selalu dari domain sendiri
        if (str_starts_with($path, 'assets/')) {
            return asset($path);
        }

        // Pastikan path dimulai dengan 'storage/' untuk pengecekan lokal & url
        $checkPath = $path;
        if (!str_starts_with($checkPath, 'storage/')) {
            $checkPath = 'storage/' . $checkPath;
        }

        // Jika file ada di folder lokal, selalu gunakan asset dari domain sendiri
        if (file_exists(public_path($checkPath))) {
            return asset($checkPath);
        }

        // Jika tidak ada di lokal dan ini mode clone/replica (master_url diset), arahkan ke master
        $masterUrl = config('app.master_url');
        if ($masterUrl) {
            return rtrim($masterUrl, '/') . '/' . $checkPath;
        }

        // Default: gunakan asset lokal
        return asset($checkPath);
    }
}

if (!function_exists('format_saldo_badge')) {
    /**
     * Format saldo nominal as a dynamic badge:
     * - Saldo < 0 (Negatif) : Merah (bg-danger text-white)
     * - Saldo >= 0 (Rp 0 & Positif) : Hijau (bg-success text-white)
     *
     * @param float|int|null $amount
     * @return string HTML badge element
     */
    function format_saldo_badge($amount): string
    {
        $val = (float) ($amount ?? 0);
        $formatted = 'Rp ' . number_format($val, 0, ',', '.');
        if ($val < 0) {
            return '<span class="badge bg-danger text-white">' . $formatted . '</span>';
        }
        return '<span class="badge bg-success text-white">' . $formatted . '</span>';
    }
}


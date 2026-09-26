<?php

namespace App\Traits;

trait HasAvatarUrl
{
    /**
     * Get the full URL for the avatar.
     * Supports absolute URLs, assets, storage paths, and relative file paths.
     * Prevents double 'storage/storage/' duplication when avatar path already contains 'storage/' or 'assets/'.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $value = $this->attributes['avatar'] ?? null;

        if (!$value || trim($value) === '') {
            return asset('assets/media/avatars/default.png');
        }

        // URL absolut → langsung return
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        $cleanPath = ltrim($value, '/');

        // Jika path sudah diawali 'storage/' atau 'assets/'
        if (str_starts_with($cleanPath, 'storage/') || str_starts_with($cleanPath, 'assets/')) {
            return function_exists('storage_asset') ? \storage_asset($cleanPath) : asset($cleanPath);
        }

        return function_exists('storage_asset') ? \storage_asset('storage/' . $cleanPath) : asset('storage/' . $cleanPath);
    }

    public function getAvatarFallbackUrlAttribute(): string
    {
        return asset('assets/media/avatars/default.png');
    }
}

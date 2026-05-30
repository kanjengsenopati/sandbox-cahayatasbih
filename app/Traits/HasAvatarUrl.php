<?php

namespace App\Traits;

trait HasAvatarUrl
{
    /**
     * Get the full URL for the avatar.
     * Supports absolute URLs, assets, storage paths, and relative file paths.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $value = $this->attributes['avatar'] ?? null;

        if (!$value) {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        $path = ltrim($value, '/');

        if (str_starts_with($path, 'storage/') || str_starts_with($path, 'assets/')) {
            return asset($path);
        }

        return asset('storage/' . $path);
    }

    public function getAvatarFallbackUrlAttribute(): string
    {
        return asset('assets/media/avatars/default.png');
    }
}

<?php

namespace App\Traits;

trait HasAvatarUrl
{
    /**
     * Get the full URL for the avatar.
     * Supports absolute URLs, assets, storage paths, and relative file paths.
     * When MASTER_APP_URL is set (clone mode), redirects storage URLs to master server.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        $value = $this->attributes['avatar'] ?? null;

        if (!$value) {
            return null;
        }

        // URL absolut → langsung return
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        if (function_exists('storage_asset')) {
            return \storage_asset($value);
        }

        return asset('storage/' . ltrim($value, '/'));
    }

    public function getAvatarFallbackUrlAttribute(): string
    {
        return asset('assets/media/avatars/default.png');
    }
}

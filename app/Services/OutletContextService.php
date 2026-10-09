<?php

namespace App\Services;

use App\Models\Outlet;
use Illuminate\Support\Facades\Cache;

class OutletContextService
{
    public const CODE_KOPERASI = 'KPR';
    public const CACHE_KEY_KOPERASI_ID = 'system_koperasi_outlet_id';

    /**
     * Get the primary Koperasi outlet ID dynamically from the database with caching.
     */
    public static function getKoperasiOutletId(): ?string
    {
        return Cache::remember(self::CACHE_KEY_KOPERASI_ID, 86400, function () {
            $outlet = Outlet::where('code', self::CODE_KOPERASI)
                ->orWhere('name', 'Koperasi')
                ->first();

            return $outlet ? $outlet->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';
        });
    }

    /**
     * Clear cached outlet IDs.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_KOPERASI_ID);
        Cache::forget('koperasi_outlet_id');
    }
}

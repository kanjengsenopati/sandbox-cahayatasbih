<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class LaporPakSetting extends Model
{
    use HasFactory;

    protected $table = 'lapor_pak_settings';

    protected $fillable = [
        'is_active',
        'start_datetime',
        'end_datetime',
        'closed_message',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    /**
     * Get or create the single setting row safely
     */
    public static function getSetting(): self
    {
        try {
            if (!Schema::hasTable('lapor_pak_settings')) {
                return new self([
                    'is_active' => true,
                    'closed_message' => 'Form pengaduan Lapor Pak saat ini tidak aktif / di luar periode pengaduan.',
                ]);
            }

            return self::firstOrCreate(
                ['id' => 1],
                [
                    'is_active' => true,
                    'closed_message' => 'Form pengaduan Lapor Pak saat ini tidak aktif / di luar periode pengaduan. Silakan periksa kembali jadwal pengaduan.',
                ]
            );
        } catch (\Throwable $e) {
            return new self([
                'is_active' => true,
                'closed_message' => 'Form pengaduan Lapor Pak saat ini tidak aktif / di luar periode pengaduan.',
            ]);
        }
    }
}

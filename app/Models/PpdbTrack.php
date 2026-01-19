<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpdbTrack extends Model
{
    const TYPE_UMUM = 'UMUM';
    const TYPE_JAMAAH = 'JAMAAH';
    const TYPE_ALUMNI = 'ALUMNI';

    use HasFactory, UuidTrait, SoftDeletes;

    protected $table = 'ppdb_tracks';

    protected $fillable = [
        'ppdb_wave_id',
        'school_id',
        'registration_type',
        'name',
        'registration_fee',
        'quota',
        'is_open',
        'close_reason',
        'link_whatsapp_group',
        'bill_type_id',
        'installment_plan',
    ];

    protected $casts = [
        'installment_plan' => 'array',
        'registration_fee' => 'integer',
        'quota' => 'integer',
        'is_open' => 'boolean',
    ];

    public function getListRegistrationTypes()
    {
        return [
            self::TYPE_UMUM => 'Non Jamaah',
            self::TYPE_JAMAAH => 'Jamaah',
            self::TYPE_ALUMNI => 'Alumni',
        ];
    }

    public function ppdbWave()
    {
        return $this->belongsTo(PpdbWaves::class, 'ppdb_wave_id')->withTrashed();
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id')->withTrashed();
    }

    public function billType()
    {
        return $this->belongsTo(BillType::class, 'bill_type_id')->withTrashed();
    }

    /**
     * Get registered count for this track
     */
    public function registrations()
    {
        return $this->hasMany(PpdbRegistration::class, 'ppdb_track_id');
    }

    /**
     * Get total installment amount
     */
    public function getTotalInstallmentAttribute(): int
    {
        if (!$this->installment_plan) {
            return 0;
        }
        return collect($this->installment_plan)->sum('amount');
    }

    /**
     * Get registered count
     */
    public function getRegisteredCountAttribute(): int
    {
        return $this->registrations()->count();
    }
}

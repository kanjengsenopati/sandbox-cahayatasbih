<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use UuidTrait;

    protected $fillable = [
        'presensiable_type',
        'presensiable_id',
        'activity_type',
        'activity_name',
        'schedule_id',
        'check_in',
        'check_out',
        'status',
        'late_minutes',
        'method',
        'device_id',
        'latitude',
        'longitude',
        'photo_path',
        'notes',
        'approval_status',
        'approved_by',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'late_minutes' => 'integer',
    ];

    public function presensiable()
    {
        return $this->morphTo();
    }

    public function device()
    {
        return $this->belongsTo(BiometricDevice::class, 'device_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public static function formatLateMinutes(?int $minutes): string
    {
        if (!$minutes || $minutes <= 0) {
            return '-';
        }
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d Jam, %02d Menit', $hours, $mins);
    }

    public function getFormattedLateTimeAttribute(): string
    {
        return static::formatLateMinutes($this->late_minutes);
    }
}

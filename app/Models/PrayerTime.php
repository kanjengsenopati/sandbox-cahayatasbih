<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;

class PrayerTime extends Model
{
    use UuidTrait;

    protected $fillable = [
        'prayer_name',
        'adzan_time',
        'iqomah_time',
        'grace_period',
    ];

    protected $casts = [
        'grace_period' => 'integer',
    ];
}

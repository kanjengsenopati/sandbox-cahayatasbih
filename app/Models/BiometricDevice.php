<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BiometricDevice extends Model
{
    use UuidTrait, SoftDeletes;

    protected $fillable = [
        'device_name',
        'device_ip',
        'location',
        'auth_token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

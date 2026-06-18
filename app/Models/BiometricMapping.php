<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;

class BiometricMapping extends Model
{
    use UuidTrait;

    protected $fillable = [
        'presensiable_type',
        'presensiable_id',
        'biometric_type',
        'biometric_index',
        'device_pin',
        'template_data',
    ];

    public function presensiable()
    {
        return $this->morphTo();
    }
}

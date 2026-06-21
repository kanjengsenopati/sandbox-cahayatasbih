<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkingShift extends Model
{
    use UuidTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'grace_period',
        'is_active',
        'target_type',
        'days',
        'assigned_users',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'grace_period' => 'integer',
        'days' => 'array',
        'assigned_users' => 'array',
    ];

    public function monthlyShifts()
    {
        return $this->hasMany(EmployeeMonthlyShift::class);
    }
}

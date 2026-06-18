<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use UuidTrait;

    protected $fillable = [
        'presensiable_type',
        'presensiable_id',
        'base_salary',
        'attendance_allowance',
        'transport_allowance',
        'lateness_penalty_per_minute',
        'absence_penalty',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'attendance_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'lateness_penalty_per_minute' => 'decimal:2',
        'absence_penalty' => 'decimal:2',
    ];

    public function presensiable()
    {
        return $this->morphTo();
    }
}

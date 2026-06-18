<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;

class EmployeeMonthlyShift extends Model
{
    use UuidTrait;

    protected $fillable = [
        'presensiable_type',
        'presensiable_id',
        'working_shift_id',
        'date',
        'is_holiday',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_holiday' => 'boolean',
    ];

    public function presensiable()
    {
        return $this->morphTo();
    }

    public function workingShift()
    {
        return $this->belongsTo(WorkingShift::class);
    }
}

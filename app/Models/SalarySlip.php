<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;

class SalarySlip extends Model
{
    use UuidTrait;

    protected $fillable = [
        'presensiable_type',
        'presensiable_id',
        'period_start',
        'period_end',
        'total_present_days',
        'total_late_minutes',
        'total_absent_days',
        'base_salary',
        'total_attendance_allowance',
        'total_transport_allowance',
        'total_lateness_penalty',
        'total_absence_penalty',
        'net_salary',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_present_days' => 'integer',
        'total_late_minutes' => 'integer',
        'total_absent_days' => 'integer',
        'base_salary' => 'decimal:2',
        'total_attendance_allowance' => 'decimal:2',
        'total_transport_allowance' => 'decimal:2',
        'total_lateness_penalty' => 'decimal:2',
        'total_absence_penalty' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function presensiable()
    {
        return $this->morphTo();
    }

    public function approver()
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }
}

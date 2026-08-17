<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    use HasFactory, UuidTrait;

    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_ROLLED_BACK = 'ROLLED_BACK';

    protected $fillable = [
        'admin_id',
        'school_id',
        'classroom_info',
        'academic_year_id',
        'bill_type_id',
        'total_students',
        'total_amount',
        'status',
        'rolled_back_at',
        'rolled_back_by',
    ];

    protected $casts = [
        'total_students' => 'integer',
        'total_amount' => 'integer',
        'rolled_back_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function billType()
    {
        return $this->belongsTo(BillType::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'import_log_id');
    }

    public function rolledBackByAdmin()
    {
        return $this->belongsTo(Admin::class, 'rolled_back_by');
    }
}

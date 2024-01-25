<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'bill_type_id',
        'student_id',
        'classroom_id',
        'academic_year_id',
        'month',
        'amount',
        'status',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function billType()
    {
        return $this->belongsTo(BillType::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}

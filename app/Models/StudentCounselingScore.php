<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCounselingScore extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'student_id',
        'classroom_id',
        'academic_year_id',
        'school_id',
        'semester',
        'score',
        'violation',
        'action',
        'note'
    ];

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

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}

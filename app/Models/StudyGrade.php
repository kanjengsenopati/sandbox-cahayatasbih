<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudyGrade extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'study_id',
        'student_id',
        'classroom_id',
        'academic_year_id',
        'semester_id',
        'grade',
        'letter_grade',
        'kkm'
    ];

    public function study()
    {
        return $this->belongsTo(Study::class)->withTrashed();
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class)->withTrashed();
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class)->withTrashed();
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class)->withTrashed();
    }
}

<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentAchievement extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'classroom_id',
        'semester',
        'title',
        'champion',
        'level',
        'reward',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class)->withTrashed();
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class)->withTrashed();
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}

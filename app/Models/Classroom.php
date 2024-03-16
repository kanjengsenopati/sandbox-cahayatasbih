<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classroom extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'school_id',
    ];


    public function school()
    {
        return $this->belongsTo(School::class)->withTrashed();
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function studyGrades()
    {
        return $this->hasMany(StudyGrade::class);
    }
}

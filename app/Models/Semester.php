<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Semester extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = ['name', 'order'];

    public function studyGrades()
    {
        return $this->hasMany(StudyGrade::class)->withTrashed();
    }
}

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
        'allow_pwa_login',
        'show_pwa_saldo',
        'allow_pwa_saldo_payment',
        'is_saldo_limit_active',
        'saldo_limit',
    ];

    protected $casts = [
        'allow_pwa_login' => 'boolean',
        'show_pwa_saldo' => 'boolean',
        'allow_pwa_saldo_payment' => 'boolean',
        'is_saldo_limit_active' => 'boolean',
        'saldo_limit' => 'integer',
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

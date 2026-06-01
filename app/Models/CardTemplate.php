<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CardTemplate extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'academic_year_id',
        'name',
        'type',
        'background_image',
        'layout',
        'is_active',
        'exam_bill_requirements'
    ];

    protected $casts = [
        'layout' => 'array',
        'is_active' => 'boolean',
        'exam_bill_requirements' => 'array'
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class)->withTrashed();
    }
}

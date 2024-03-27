<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ppdb extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'ppdb_type_id',
        'academic_year_id',
        'school_id',
        'capacity',
        'start_date',
        'end_date',
        'description',
        'image',
        'slug',
        'is_active',
        'register_fee',
    ];

    public function ppdbType()
    {
        return $this->belongsTo(PpdbType::class)->withTrashed();
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class)->withTrashed();
    }

    public function school()
    {
        return $this->belongsTo(School::class)->withTrashed();
    }
}

<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PpdbWaves extends Model
{
    use HasFactory, SoftDeletes, UuidTrait;

    protected $table = 'ppdb_waves';
    protected $fillable = [
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id')->withTrashed();
    }

    public function tracks()
    {
        return $this->hasMany(PpdbTrack::class, 'ppdb_wave_id')->withTrashed();
    }
}

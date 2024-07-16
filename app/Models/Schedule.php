<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    const TYPE_ALL = 'ALL';
    const TYPE_SCHOOL = 'SCHOOL';
    protected $fillable = [
        'school_id',
        'name',
        'description',
        'date',
        'type',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    protected $appends = [
        'translated_month',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function getTranslatedMonthAttribute()
    {
        return $this->date->translatedFormat('F');
    }
}

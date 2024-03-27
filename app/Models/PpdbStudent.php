<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpdbStudent extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'ppdb_registration_id',
        'name',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'address',
        'nisn',
        'nik',
        'origin_school',
    ];

    public function ppdbRegistrations()
    {
        return $this->hasMany(PpdbRegistration::class)->withTrashed();
    }
}

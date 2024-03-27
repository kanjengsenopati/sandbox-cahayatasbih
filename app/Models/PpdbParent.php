<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpdbParent extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'family_card_number',
        'father_name',
        'father_nik',
        'father_status',
        'father_education',
        'father_job',
        'mother_name',
        'mother_nik',
        'mother_status',
        'mother_education',
        'mother_job',
        'government_aid_card_type',
        'is_member',
        'mdti_branch',
        'member_number',
        'photo_card',
    ];

    public function ppdbRegistrations()
    {
        return $this->hasMany(PpdbRegistration::class)->withTrashed();
    }
}

<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpdbDocument extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'ppdb_registration_id',
        'family_card_image',
        'birth_certificate_image',
        'raport_image',
        'father_identity_image',
        'mother_identity_image',
    ];

    public function ppdbRegistration()
    {
        return $this->belongsTo(PpdbRegistration::class);
    }
}

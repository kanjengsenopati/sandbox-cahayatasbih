<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpdbRegistration extends Model
{
    const STATUS_ACCEPTED = 'ACCEPTED';
    const STATUS_KTA_REVISION = 'KTA_REVISION';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_PENDING  = 'PENDING';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'user_id',
        'ppdb_track_id',
        'registration_code',
        'status',
        'payment_status',
        'payment_proof',
        'admin_note',
        'name',
        'nisn',
        'nik',
        'birth_place',
        'birth_date',
        'gender',
        'student_phone',
        'student_email',
        'address_street',
        'rt',
        'rw',
        'village',
        'district',
        'city',
        'postal_code',
        'kk_number',
        'father_name',
        'father_nik',
        'father_status',
        'father_job',
        'mother_name',
        'mother_nik',
        'mother_status',
        'mother_job',
        'parent_phone',
        'is_mdti_member',
        'mdti_branch',
        'mdti_group',
        'origin_school',
        'origin_school_address',
        'medical_history',
        'achievements',
        'gov_assistance',
        'hobby',
        'ambition',
        'motivation',
        'kta',
    ];

    protected $appends = [
        'registration_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function track()
    {
        return $this->belongsTo(PpdbTrack::class, 'ppdb_track_id')->withTrashed();
    }

    public function getRegistrationTypeAttribute()
    {
        return $this->track ? $this->track->name : '-';
    }
}

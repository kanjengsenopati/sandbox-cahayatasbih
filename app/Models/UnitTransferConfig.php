<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitTransferConfig extends Model
{
    use HasFactory, UuidTrait;

    protected $fillable = [
        'from_school_id',
        'eligible_class_level',
        'to_school_id',
        'to_classroom_id',
        'bill_type_id',
        'amount',
        'is_active',
    ];

    public function fromSchool()
    {
        return $this->belongsTo(School::class, 'from_school_id')->withTrashed();
    }

    public function toSchool()
    {
        return $this->belongsTo(School::class, 'to_school_id')->withTrashed();
    }

    public function toClassroom()
    {
        return $this->belongsTo(Classroom::class, 'to_classroom_id')->withTrashed();
    }

    public function billType()
    {
        return $this->belongsTo(BillType::class, 'bill_type_id')->withTrashed();
    }
}

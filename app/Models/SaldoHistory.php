<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaldoHistory extends Model
{
    const TYPE_IN = 'IN';
    const TYPE_OUT = 'OUT';
    const STATUS_PENDING = 'PENDING';
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAILED = 'FAILED';
    const TYPE_BLOCK = "BLOCKED";
    const TYPE_UNBLOCKED = "UNBLOCKED";
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'student_id',
        'type',
        'amount',
        'description',
        'status'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}

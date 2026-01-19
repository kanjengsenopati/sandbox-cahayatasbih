<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentBillNotification extends Model
{
    const STATUS_PENDING = 'PENDING';
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAILED = 'FAILED';
    use HasFactory, UuidTrait;

    protected $fillable = [
        'student_id',
        'message',
        'status',
        'sent_at',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }
}

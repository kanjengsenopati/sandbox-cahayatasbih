<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentRateStudent extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'payment_rate_id',
        'student_id',
    ];

    public function paymentRate()
    {
        return $this->belongsTo(PaymentRate::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}

<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentRateClassroom extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'payment_rate_id',
        'classroom_id',
    ];

    public function paymentRate()
    {
        return $this->belongsTo(PaymentRate::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}

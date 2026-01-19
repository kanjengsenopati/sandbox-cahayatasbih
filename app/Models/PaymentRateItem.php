<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentRateItem extends Model
{
    use HasFactory, SoftDeletes, UuidTrait;

    protected $fillable = [
        'payment_rate_id',
        'month',
        'year',
        'amount',
    ];


    public function paymentRate()
    {
        return $this->belongsTo(PaymentRate::class)->withTrashed();
    }
}

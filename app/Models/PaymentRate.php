<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentRate extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'bill_type_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function billType()
    {
        return $this->belongsTo(BillType::class)->withTrashed();
    }

    public function paymentRateClassrooms()
    {
        return $this->hasMany(PaymentRateClassroom::class)->withTrashed();
    }

    public function paymentRateItems()
    {
        return $this->hasMany(PaymentRateItem::class);
    }
}

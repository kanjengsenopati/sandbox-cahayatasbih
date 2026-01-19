<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{

    use HasFactory, UuidTrait, SoftDeletes;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const AUTO_PAYMENT = 'd45cdddc-70c7-4787-bb0f-f83ab233b271';
    const CASH_PAYMENT = '72abbd5c-ee1b-4a1d-b327-4d61b9d0be9c';
    const SALDO_PAYMENT = 'ca513e57-fb61-4063-acb0-25595c6c8ea6';
    const TYPE_BALANCE = 'BALANCE';
    const TYPE_XENDIT = 'XENDIT';
    const TYPE_CASH = 'CASH';
    const TYPE_TRANSFER = 'TRANSFER';

    protected $fillable = [
        'name',
        'type',
        'is_active'
    ];

    protected $appends = [
        'translated_name'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->withTrashed();
    }

    public function getTranslatedNameAttribute()
    {
        return $this->getTypeList()[$this->type];
    }

    public function getTypeList()
    {
        return [
            self::TYPE_BALANCE => 'Saldo',
            self::TYPE_XENDIT => 'Otomatis',
            self::TYPE_CASH => 'Tunai',
            self::TYPE_TRANSFER => 'Transfer'
        ];
    }
}

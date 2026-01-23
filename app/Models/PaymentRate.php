<?php

namespace App\Models;

use App\Models\Bill;
use App\Models\BillType;
use App\Traits\UuidTrait;
use App\Models\PaymentRateItem;
use App\Models\PaymentRateClassroom;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentRate extends Model
{
    const TYPE_REGULAR = 'REGULAR';
    const TYPE_TRANSFER = 'TRANSFER';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'bill_type_id',
        'amount',
        'type',
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

    public function paymentRateStudents()
    {
        return $this->hasMany(PaymentRateStudent::class)->withTrashed();
    }

    public function paymentRateItems()
    {
        return $this->hasMany(PaymentRateItem::class);
    }

    public function bills()
    {
        // Artinya: PaymentRate punya banyak Bill, MELALUI perantara PaymentRateItem
        return $this->hasManyThrough(
            Bill::class,            // Model Tujuan (Tagihan)
            PaymentRateItem::class, // Model Perantara
            'payment_rate_id',      // Foreign Key di tabel perantara (payment_rate_items.payment_rate_id)
            'payment_rate_item_id', // Foreign Key di tabel tujuan (bills.payment_rate_item_id)
            'id',                   // Local Key di tabel asal (payment_rates.id)
            'id'                    // Local Key di tabel perantara (payment_rate_items.id)
        );
    }

    // Opsional: Accessor untuk label yang ramah user
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            self::TYPE_REGULAR => 'Reguler',
            self::TYPE_TRANSFER => 'Susulan / Pindahan',
            default => 'Lainnya',
        };
    }
}

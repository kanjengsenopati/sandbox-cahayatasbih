<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillType extends Model
{
    const TYPE_MONTHLY = 'MONTHLY';
    const TYPE_OTHER = 'OTHER';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'bill_item_id',
        'academic_year_id',
        'name',
        'type',
    ];

    public function billItem()
    {
        return $this->belongsTo(BillItem::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function paymentRates()
    {
        return $this->hasMany(PaymentRate::class);
    }
}

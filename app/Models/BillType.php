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
        return $this->belongsTo(BillItem::class)->withTrashed();
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class)->withTrashed();
    }

    public function paymentRates()
    {
        return $this->hasMany(PaymentRate::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function billTypeBank()
    {
        return $this->hasMany(BillTypeBank::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($billType) {
            $billType->bills()->delete();
        });

        static::restoring(function ($billType) {
            $billType->bills()->withTrashed()->restore();
        });
    }
}

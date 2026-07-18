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
        'payment_input_type',
        'use_wali_filter',
        'use_gender_filter',
    ];

    protected $casts = [
        'use_wali_filter' => 'boolean',
        'use_gender_filter' => 'boolean',
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

    public function getSchoolTypeAttribute()
    {
        return $this->rememberSchoolInfo()['type'] ?? null;
    }

    public function getSchoolNameAttribute()
    {
        return $this->rememberSchoolInfo()['name'] ?? null;
    }

    public function getFormattedNameAttribute()
    {
        $suffix = $this->school_type;
        if ($suffix && !str_contains(strtolower($this->name), strtolower($suffix))) {
            return $this->name . ' ' . $suffix;
        }
        return $this->name;
    }

    private function rememberSchoolInfo()
    {
        return \Illuminate\Support\Facades\Cache::remember("bill_type_{$this->id}_school_info", 3600, function() {
            $bill = $this->bills()->first();
            if ($bill && $bill->student && $bill->student->classroom && $bill->student->classroom->school) {
                return [
                    'type' => $bill->student->classroom->school->type,
                    'name' => $bill->student->classroom->school->name,
                ];
            }

            // Fallback: Cari dari tarif pembayaran (PaymentRate -> PaymentRateClassroom -> Classroom -> School)
            $rate = $this->paymentRates()->first();
            if ($rate) {
                $rateClassroom = $rate->paymentRateClassrooms()->first();
                if ($rateClassroom && $rateClassroom->classroom && $rateClassroom->classroom->school) {
                    return [
                        'type' => $rateClassroom->classroom->school->type,
                        'name' => $rateClassroom->classroom->school->name,
                    ];
                }
            }
            return [];
        });
    }

    protected static function booted()
    {
        static::deleting(function ($billType) {
            $billType->bills()->delete();
        });

        static::restoring(function ($billType) {
            $billType->bills()->withTrashed()->restore();
        });
    }
}

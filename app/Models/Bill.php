<?php

namespace App\Models;

use App\Models\Traits\GeneralTrait;
use Carbon\Carbon;
use App\Traits\UuidTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bill extends Model
{

    const STATUS_UNPAID = 'UNPAID';
    const STATUS_PAID = 'PAID';
    use HasFactory, UuidTrait, SoftDeletes, GeneralTrait;

    protected $fillable = [
        'bill_type_id',
        'student_id',
        'classroom_id',
        'academic_year_id',
        'month',
        'amount',
        'status',
        'year',
        'payment_rate_item_id',
    ];

    protected $casts = [
        'amount' => 'integer',
        'month' => 'integer',
    ];

    protected $appends = [
        'translated_month',
        'translated_status',
        // 'paid_date',
        // 'payment_method',
    ];

    public static $monthOrder = [
        1 => 1,   // Januari
        2 => 2,   // Februari
        3 => 3,   // Maret
        4 => 4,   // April
        5 => 5,   // Mei
        6 => 6,   // Juni
        7 => 7,   // Juli
        8 => 8,   // Agustus
        9 => 9,   // September
        10 => 10, // Oktober
        11 => 11, // November
        12 => 12, // Desember
    ];

    public function billType()
    {
        return $this->belongsTo(BillType::class)->withTrashed();
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class)->withTrashed();
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class)->withTrashed();
    }

    public function getTranslatedMonthAttribute()
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[$this->month] ?? null; // Return the month name or null if month is invalid
    }

    // public function getTranslatedMonthAttribute()
    // {
    //     return Carbon::createFromFormat('m', $this->month)->translatedFormat('F');
    // }

    public function getTranslatedStatusAttribute()
    {
        if ($this->status == self::STATUS_UNPAID) {
            return 'Belum Lunas';
        } else {
            return 'Lunas';
        }
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class)->withTrashed();
    }

    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, TransactionDetail::class, 'bill_id', 'id', 'id', 'transaction_id');
    }

    public function getPaidTransaction()
    {
        return $this->transactions()->where('status', Transaction::STATUS_PAID)->first();
    }

    public function getPaidDateAttribute()
    {
        return $this->getPaidTransaction() ? $this->getPaidTransaction()?->paid_at : null;
    }

    public function getPaymentMethodAttribute()
    {
        return $this->getPaidTransaction() ? $this->getPaidTransaction()?->paymentMethod?->name : null;
    }

    // get list bank for bill from bill type
    public function banks()
    {
        return $this->hasManyThrough(Bank::class, BillTypeBank::class, 'bill_type_id', 'id', 'bill_type_id', 'bank_id');
    }

    public function scopeHasSchool($query)
    {
        $query->whereHas('student', function ($query) {
            $query->whereHas('classroom', function ($query) {
                $query->where('school_id', request()->school_id ?? Auth::user()->adminSchool->school_id);
            });
        });
    }

    public function paymentRateItems()
    {
        return $this->belongsTo(PaymentRateItem::class, 'payment_rate_item_id');
    }
}

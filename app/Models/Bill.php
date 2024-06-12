<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Bill extends Model
{

    const STATUS_UNPAID = 'UNPAID';
    const STATUS_PAID = 'PAID';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'bill_type_id',
        'student_id',
        'classroom_id',
        'academic_year_id',
        'month',
        'amount',
        'status',
        'year',
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

    public function billType()
    {
        return $this->belongsTo(BillType::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class)->withTrashed();
    }

    public function getTranslatedMonthAttribute()
    {
        return Carbon::createFromFormat('m', $this->month)->translatedFormat('F');
    }

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
}

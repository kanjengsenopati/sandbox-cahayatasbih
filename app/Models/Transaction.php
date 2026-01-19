<?php

namespace App\Models;

use App\Models\Traits\GeneralTrait;
use App\Traits\UuidTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    const STATUS_PENDING = 'PENDING';
    const STATUS_PENDING_PAYMENT = 'PENDING_PAYMENT';
    const STATUS_PENDING_CONFIRMATION = 'PENDING_CONFIRMATION';
    const STATUS_PAID = 'PAID';
    const STATUS_EXPIRED = 'EXPIRED';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_REJECTED = 'REJECTED';
    const TYPE_BILL = 'BILL';
    const TYPE_SALDO = 'SALDO';
    const TYPE_SAVING = 'SAVING';
    const TYPE_PPDB = 'PPDB';
    const STATUS = [
        self::STATUS_PENDING,
        self::STATUS_PAID,
        self::STATUS_EXPIRED,
        self::STATUS_CANCELLED
    ];
    use HasFactory, UuidTrait, SoftDeletes, GeneralTrait;

    protected $fillable = [
        'payment_method_id',
        'student_id',
        'payment_code',
        'expiry_time',
        'pay_amount',
        'paid_at',
        'status',
        'xendit_id',
        'payment_link',
        'xendit_fee',
        'type',
        'app_fee',
        'admin_id',
        'user_id',
        'unique_payment',
    ];

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class)->withTrashed();
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class)->withTrashed();
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function transactionProofs()
    {
        return $this->hasMany(TransactionProof::class)->withTrashed();
    }

    public function activeProof()
    {
        return $this->hasOne(TransactionProof::class)->where('is_active', true);
    }

    public function getTranslatedPaymentMethod()
    {
        return $this->paymentMethod?->translated_name ?? '';
    }

    public function getBanksAttribute()
    {
        // return $this->load('student.classroom.school.topupBank.bank');
        if ($this->type == self::TYPE_BILL) {
            $this->load('transactionDetails.bill.banks');
            return $this->transactionDetails->bill->banks->pluck('bank');
        } elseif ($this->type == self::TYPE_SALDO) {
            $this->load('student.classroom.school.saldoBank.bank');
            return $this->student->classroom->school->saldoBank->pluck('bank');
        } elseif ($this->type == self::TYPE_SAVING) {
            $this->load('student.classroom.school.savingBank.bank');
            return $this->student->classroom->school->savingBank->pluck('bank');
        }

        return collect(); // Return an empty collection if the type is unknown
    }

    public function scopeHasSchool($query)
    {
        // Assuming 'adminSchool' is a relationship returning the school IDs the admin can access
        $admin = Auth::user();
        $schoolIds = $admin?->adminSchool?->pluck('school_id');
        $query->whereHas('student', function ($query) use ($schoolIds) {
            $query->whereHas('classroom', function ($query) use ($schoolIds) {
                $query->whereHas('school', function ($query) use ($schoolIds) {
                    $query->whereIn('id', $schoolIds);
                });
            });
        });
    }
}

<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    const STATUS_PENDING = 'PENDING';
    const STATUS_PAID = 'PAID';
    const STATUS_EXPIRED = 'EXPIRED';
    const STATUS_CANCELLED = 'CANCELLED';
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
    use HasFactory, UuidTrait, SoftDeletes;

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
}

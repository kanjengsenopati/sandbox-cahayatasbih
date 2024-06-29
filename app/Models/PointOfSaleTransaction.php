<?php

namespace App\Models;

use App\Models\Admin;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PointOfSaleTransaction extends Model
{
    const TYPE_SANTRI = 'SANTRI';
    const TYPE_UMUM = 'UMUM';
    const PAYMENT_SALDO = 'Saldo';
    const PAYMENT_TUNAI = 'Tunai';
    const STATUS_PENDING = 'PENDING';
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAILED = 'FAILED';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'student_id',
        'admin_id',
        'saldo_history_id',
        'payment_code',
        'pay_amount',
        'paid_at',
        'status',
        'profit',
        'type',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function admins()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id')->withTrashed();
    }

    public function pointOfSaleTransactionDetails()
    {
        return $this->hasMany(PointOfSaleTransactionDetail::class)->withTrashed();
    }
}

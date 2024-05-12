<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpdbRegistration extends Model
{
    const STATUS_PENDING = "PENDING";
    const STATUS_PAID = "PAID";
    const STATUS_REJECTED = "REJECTED";
    const STATUS_APPROVED = "APPROVED";
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'user_id',
        'ppdb_id',
        'no_reg',
        'register_fee',
        'status',
        'payment_status',
    ];

    protected $appends = [
        'translated_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function ppdb()
    {
        return $this->belongsTo(Ppdb::class)->withTrashed();
    }

    public function ppdbStudents()
    {
        return $this->hasMany(PpdbStudent::class)->withTrashed();
    }

    public function ppdbParents()
    {
        return $this->hasMany(PpdbParent::class)->withTrashed();
    }

    public function transactionDetails()
    {
        return $this->hasMany(TransactionDetail::class)->withTrashed();
    }

    public function ppdbDocument()
    {
        return $this->hasOne(PpdbDocument::class)->withTrashed();
    }

    public function getTranslatedStatusAttribute()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'Menunggu Pembayaran';
            case self::STATUS_PAID:
                return 'Menunggu Konfirmasi';
            case self::STATUS_REJECTED:
                return 'Tidak Lolos';
            case self::STATUS_APPROVED:
                return 'Lolos';
            default:
                return '-';
        }
    }
}

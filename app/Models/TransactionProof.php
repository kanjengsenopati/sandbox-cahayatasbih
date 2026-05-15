<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionProof extends Model
{
    const STATUS_WAITING_CONFIRMATION = 'WAITING_CONFIRMATION';
    const STATUS_CONFIRMED = 'CONFIRMED';
    const STATUS_REJECTED = 'REJECTED';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'bank_id',
        'student_id',
        'proof_image',
        'status',
        'note',
        'is_active',
    ];

    protected $appends = [
        'translated_status',
        'proof_image_url'
    ];

    public function getProofImageAttribute($value)
    {
        if (!$value) return null;
        if (filter_var($value, FILTER_VALIDATE_URL)) return $value;
        return asset('storage/' . $value);
    }

    public function getProofImageUrlAttribute()
    {
        $value = $this->attributes['proof_image'] ?? null;
        if (!$value) return null;
        if (filter_var($value, FILTER_VALIDATE_URL)) return $value;
        return asset('storage/' . $value);
    }

    public function getTranslatedStatusAttribute()
    {
        $translatedStatus = null;

        switch ($this->status) {
            case self::STATUS_WAITING_CONFIRMATION:
                $translatedStatus = 'Menunggu Konfirmasi Petugas';
                break;
            case self::STATUS_CONFIRMED:
                $translatedStatus = 'Sukses';
                break;
            case self::STATUS_REJECTED:
                $translatedStatus = 'Ditolak';
                break;
            default:
                $translatedStatus = 'Menunggu Konfirmasi Petugas';
                break;
        }

        return $translatedStatus;
    }


    public function transaction()
    {
        return $this->belongsTo(Transaction::class)->withTrashed();
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class)->withTrashed();
    }

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }
}

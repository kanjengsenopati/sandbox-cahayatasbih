<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    const STATUS_ACTIVE = "ACTIVE";
    const STATUS_INACTIVE = "INACTIVE";
    const STATUS_GRADUATED = "GRADUATED";
    const STATUS_TRANSFERRED = "TRANSFERRED";
    const STATUS_DROPPED_OUT = "DROPPED_OUT";
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'nisn',
        'nis',
        'user_id',
        'school_id',
        'classroom_id',
        'name',
        'born_place',
        'birth_date',
        'gender',
        'saldo',
        'avatar',
        'barcode',
        'is_blocked',
        'daily_limit',
        'saving',
        'status',
        'address',
    ];

    protected $casts = [
        'daily_limit' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class)->withTrashed();
    }

    public function tahfidzs()
    {
        return $this->hasMany(Tahfidz::class)->withTrashed();
    }

    public function bills()
    {
        return $this->hasMany(Bill::class)->withTrashed();
    }

    public function saldoHistories()
    {
        return $this->hasMany(SaldoHistory::class)->withTrashed();
    }

    public function pointOfSaleTransactions()
    {
        return $this->hasMany(PointOfSaleTransaction::class)->withTrashed();
    }

    public function savingHistories()
    {
        return $this->hasMany(SavingHistory::class)->withTrashed();
    }

    public function scopeHasSchoolPlace($query)
    {
        // if auth user have school_id, then use it
        if (Auth::guard('web')->user()->school_id) {
            return $query->whereSchoolId(Auth::guard('web')->user()->school_id);
        }
    }

    // count total shopping this day
    public function getTotalShoppingTodayAttribute()
    {
        return $this->pointOfSaleTransactions()
            ->whereDate('paid_at', now())
            ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->sum('pay_amount') ?? 0;
    }

    public function getListStatusAttribute()
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Tidak Aktif',
            self::STATUS_GRADUATED => 'Lulus',
            self::STATUS_TRANSFERRED => 'Pindah',
            self::STATUS_DROPPED_OUT => 'Keluar',
        ];
    }

    // on create generate barcode
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->barcode = Str::random(17);
        });
    }
}

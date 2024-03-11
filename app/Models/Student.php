<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
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
}

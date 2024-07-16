<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaldoHistory extends Model
{
    const TYPE_IN = 'IN';
    const TYPE_OUT = 'OUT';
    const STATUS_PENDING = 'PENDING';
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAILED = 'FAILED';
    const TYPE_BLOCK = "BLOCKED";
    const TYPE_UNBLOCKED = "UNBLOCKED";
    const USAGE_BILL = "BILL";
    const USAGE_TOPUP = "TOPUP";
    const USAGE_POS = "POS";
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'student_id',
        'type',
        'amount',
        'description',
        'status',
        'usage'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function pointOfSaleTransaction()
    {
        return $this->hasOne(PointOfSaleTransaction::class)->withTrashed();
    }

    public function cashier()
    {
        return $this->belongsToMany(Admin::class, 'point_of_sale_transactions', 'saldo_history_id', 'admin_id');
    }

    public function transaction_details()
    {
        return $this->hasMany(TransactionDetail::class)->withTrashed();
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

<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SavingHistory extends Model
{
    const TYPE_IN = 'IN';
    const TYPE_OUT = 'OUT';
    const STATUS_PENDING = 'PENDING';
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_FAILED = 'FAILED';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'student_id',
        'admin_id',
        'type',
        'amount',
        'description',
        'status',
        'date',
    ];


    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class)->withTrashed();
    }

    public function transactionDetail()
    {
        return $this->hasOne(TransactionDetail::class);
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

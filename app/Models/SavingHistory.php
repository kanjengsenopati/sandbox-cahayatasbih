<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}

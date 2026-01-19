<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashFlow extends Model
{
    const TYPE_INCOME = 'INCOME';
    const TYPE_EXPENSE = 'EXPENSE';

    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';

    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'cash_flow_category_id',
        'payment_code',
        'type',
        'amount',
        'date',
        'description',
        'status',
        'proof_of_payment',
        'payment_method',
        'reason',
    ];

    public function sender()
    {
        return $this->belongsTo(Admin::class, 'sender_id')->withTrashed();
    }

    public function receiver()
    {
        return $this->belongsTo(Admin::class, 'receiver_id')->withTrashed();
    }

    public function cashflow_category()
    {
        return $this->belongsTo(CashFlowCategory::class, 'cash_flow_category_id')->withTrashed();
    }
}

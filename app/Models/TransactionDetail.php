<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionDetail extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable  = [
        'transaction_id',
        'bill_id',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class)->withTrashed();
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class)->withTrashed();
    }
}

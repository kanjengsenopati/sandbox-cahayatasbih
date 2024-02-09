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
        'parentable_type',
        'parentable_id',
    ];

    public function parent()
    {
        return $this->morphTo('parent', 'parentable_type', 'parentable_id')->withTrashed();
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class)->withTrashed();
    }
}

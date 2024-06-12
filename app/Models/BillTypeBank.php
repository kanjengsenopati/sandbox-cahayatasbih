<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillTypeBank extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'bank_id',
        'bill_type_id'
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class)->withTrashed();
    }

    public function billType()
    {
        return $this->belongsTo(BillType::class)->withTrashed();
    }
}

<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TopupBank extends Model
{
    const TYPE_SALDO = 'SALDO';
    const TYPE_SAVING = 'SAVING';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'bank_id',
        'school_id',
        'type',
    ];


    public function bank()
    {
        return $this->belongsTo(Bank::class)->withTrashed();
    }

    public function school()
    {
        return $this->belongsTo(School::class)->withTrashed();
    }
}

<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'account_number',
        'account_name',
        'image',
        'is_active'
    ];

    public function billTypeBank()
    {
        return $this->hasMany(BillTypeBank::class);
    }
}

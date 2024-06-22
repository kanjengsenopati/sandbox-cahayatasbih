<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    const TYPE_SUPERADMIN = 'SUPERADMIN';
    const TYPE_BENDAHARA = 'BENDAHARA';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'type',
    ];

    public function getListType()
    {
        return [
            self::TYPE_SUPERADMIN => 'SUPERADMIN', // 'Super Admin
            self::TYPE_BENDAHARA => 'BENDAHARA', // 'Bendahara'
        ];
    }
}

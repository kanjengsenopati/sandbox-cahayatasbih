<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Help extends Model
{
    const TYPE_WHATSAPP = 'WHATSAPP';
    const TYPE_EMAIL = 'EMAIL';
    const TYPE_PHONE = 'PHONE';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'contact',
        'image'
    ];


    public function getTypes()
    {
        return [
            self::TYPE_WHATSAPP => 'WhatsApp',
            self::TYPE_EMAIL => 'Email',
            self::TYPE_PHONE => 'Phone',
        ];
    }
}

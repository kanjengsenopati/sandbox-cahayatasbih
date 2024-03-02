<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationSetting extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'payment_fee',
        'student_card_image',
        'payment_expire_time',
        'link_whatsapp',
        'number_whatsapp',
        'device_id',
    ];
}

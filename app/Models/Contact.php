<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory, UuidTrait;

    protected $fillable = [
        'email',
        'phone',
        'address',
        'facebook',
        'twitter',
        'instagram',
        'youtube',
        'linkedin',
        'maps'
    ];
}

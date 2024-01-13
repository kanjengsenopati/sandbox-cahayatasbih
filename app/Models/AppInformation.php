<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppInformation extends Model
{
    use HasFactory, UuidTrait;

    protected $fillable = [
        'privacy_policy',
        'privacy_policy_en',
        'terms_and_conditions',
        'terms_and_conditions_en',
        'disclaimer',
        'disclaimer_en',
    ];
}

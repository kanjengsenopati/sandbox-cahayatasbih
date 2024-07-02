<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppInformation extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $table = 'app_information';

    protected $fillable = [
        'terms_and_conditions',
        'privacy_policy',
        'about_us',
    ];
}

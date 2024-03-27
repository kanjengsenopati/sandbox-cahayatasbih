<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PpdbType extends Model
{
    const TYPE_NON_JAMAAH = 'NON_JAMAAH';
    const TYPE_JAMAAH = 'JAMAAH';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'is_active',
        'type'
    ];
}

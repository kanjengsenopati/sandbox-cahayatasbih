<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuNavigation extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'icon',
        'order', // Add this line (1/2)
        'is_active',
    ];


    public function subMenuNavigation()
    {
        return $this->hasMany(SubMenuNavigation::class);
    }
}

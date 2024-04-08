<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubMenuNavigation extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'menu_navigation_id',
        'name',
        'url',
        'order',
        'is_active',
        'permission',
    ];


    public function menuNavigation()
    {
        return $this->belongsTo(MenuNavigation::class)->withTrashed();
    }
}

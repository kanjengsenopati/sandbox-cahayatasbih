<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'is_active'
    ];

    public function adminOutlet()
    {
        return $this->hasMany(AdminOutlet::class, 'outlet_id');
    }
}

<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'name'
    ];

    public function admin()
    {
        return $this->hasMany(Admin::class);
    }
}

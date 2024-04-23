<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    const TYPE_SMP = 'SMP';
    const TYPE_MA = 'MA';
    const TYPE_PONDOK = 'PONDOK';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'name'
    ];

    public function admin()
    {
        return $this->hasMany(Admin::class)->withTrashed();
    }

    public function classroom()
    {
        return $this->hasMany(Classroom::class)->withTrashed();
    }
}

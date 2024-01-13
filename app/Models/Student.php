<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'born_place',
        'birth_date',
        'gender',
        'saldo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

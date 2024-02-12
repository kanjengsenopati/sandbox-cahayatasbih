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
        'school_id',
        'classroom_id',
        'name',
        'born_place',
        'birth_date',
        'gender',
        'saldo',
        'nisn',
        'avatar',
        'barcode',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class)->withTrashed();
    }

    public function tahfidzs()
    {
        return $this->hasMany(Tahfidz::class)->withTrashed();
    }

    public function bills()
    {
        return $this->hasMany(Bill::class)->withTrashed();
    }
}

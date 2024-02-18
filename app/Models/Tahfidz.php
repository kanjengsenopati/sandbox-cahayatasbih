<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tahfidz extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'student_id',
        'number_of_pages',
        'deposit_date',
        'note',
        'feedback',
        'link',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function getCreatedAtAttribute($value)
    {
        return date('Y-m-d H:i:s', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('Y-m-d H:i:s', strtotime($value));
    }
}

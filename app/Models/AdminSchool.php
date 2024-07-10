<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminSchool extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    // define the table name
    protected $table = 'admin_schools';
    protected $fillable = [
        'admin_id',
        'school_id',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class)->withTrashed();
    }

    public function school()
    {
        return $this->belongsTo(School::class)->withTrashed();
    }
}

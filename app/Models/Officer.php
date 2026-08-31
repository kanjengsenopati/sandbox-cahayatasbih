<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Admin;

class Officer extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $appends = ['name'];

    protected $fillable = [
        'position',
        'duty',
        'phone',
        'photo',
        'is_active',
        'is_cs_password',
        'admin_id',
    ];

    /**
    * Relasi ke Admin yang mewakili petugas ini.
    */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
    * Relasi ke User (diarahkan ke Admin agar tidak memecah PWA/API)
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Accessor untuk menampilkan nama petugas melalui Admin.
     */
    public function getNameAttribute()
    {
        return $this->admin ? $this->admin->name : null;
    }
}


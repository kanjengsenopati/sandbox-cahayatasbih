<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Officer extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        // 'name' dihapus, nama diambil dari relasi User
        'position',
        'duty',
        'phone',
        'photo',
        'is_active',
        'user_id',
    ];
    /**
    * Relasi ke User yang mewakili petugas ini.
    */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor untuk menampilkan nama petugas melalui User.
     */
    public function getNameAttribute()
    {
        return $this->user ? $this->user->name : null;
    }
}


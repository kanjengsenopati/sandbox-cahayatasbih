<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asrama extends Model
{
    use HasFactory, UuidTrait;

    protected $fillable = [
        'name',
        'host_admin_id',
    ];

    /**
     * Get the host admin (ustadz) supervising this dormitory.
     */
    public function hostAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'host_admin_id');
    }

    /**
     * Get the students belonging to this dormitory.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'asrama_id');
    }

    /**
     * Boot function to propagate changes on update.
     */
    /**
     * Boot function to propagate changes on update.
     * Uses booted() instead of boot() to avoid overriding UuidTrait::boot()
     */
    protected static function booted()
    {
        // When asrama details are updated, automatically sync child students to keep data consistent
        static::updated(function ($asrama) {
            $asrama->students()->update([
                'asrama_name' => $asrama->name,
                'asrama_host_id' => $asrama->host_admin_id,
            ]);
        });
    }
}

<?php

namespace App\Models;

use App\Traits\LogActivityTrait;
use App\Traits\UuidTrait;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory, HasRoles, UuidTrait, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'is_active',
        'role_id',
        'school_id',
        'last_login_at',
        'access_scope',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */




    public function GetRoleNameAttribute()
    {
        return $this->roles()->first()->name;
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function stockHistories(): HasMany
    {
        return $this->hasMany(StockHistory::class);
    }

    public function pointOfSaleTransactions(): HasMany
    {
        return $this->hasMany(PointOfSaleTransaction::class);
    }

    public function adminSchool(): HasMany
    {
        return $this->hasMany(AdminSchool::class);
    }
}

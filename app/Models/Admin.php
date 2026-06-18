<?php

namespace App\Models;

use App\Traits\HasAvatarUrl;
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
    use HasFactory, HasRoles, UuidTrait, SoftDeletes, HasAvatarUrl, LogActivityTrait;

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
        'outlet_id',
        'last_login_at',
        'access_scope',
        'fcm_token',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'avatar_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_login_at' => 'datetime',
    ];

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

    public function adminOutlet(): HasMany
    {
        return $this->hasMany(AdminOutlet::class);
    }

    /**
     * Ambil semua outlet_id yang diassign ke admin ini
     * (dari tabel pivot admin_outlets + outlet_id utama)
     */
    public function getOutletIds(): array
    {
        $outletIds = $this->adminOutlet->pluck('outlet_id')->toArray();
        // Sertakan outlet_id utama jika ada dan belum ada di list
        if ($this->outlet_id && !in_array($this->outlet_id, $outletIds)) {
            $outletIds[] = $this->outlet_id;
        }
        return $outletIds;
    }

    public function attendances()
    {
        return $this->morphMany(Attendance::class, 'presensiable');
    }

    public function biometricMappings()
    {
        return $this->morphMany(BiometricMapping::class, 'presensiable');
    }

    public function employeeSalary()
    {
        return $this->morphOne(EmployeeSalary::class, 'presensiable');
    }

    public function salarySlips()
    {
        return $this->morphMany(SalarySlip::class, 'presensiable');
    }

    public function monthlyShifts()
    {
        return $this->morphMany(EmployeeMonthlyShift::class, 'presensiable');
    }
}


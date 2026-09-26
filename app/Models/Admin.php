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
        if ($this->roles->isEmpty()) {
            if ($this->role_id) {
                $r = \Spatie\Permission\Models\Role::find($this->role_id);
                if ($r) {
                    return $r->name;
                }
            }
            return 'Admin';
        }
        return $this->roles->pluck('name')->implode(', ');
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

    /**
     * Ambil semua school_id yang diassign ke admin ini
     * (dari tabel pivot admin_schools + school_id utama)
     */
    public function getSchoolIds(): array
    {
        $schoolIds = $this->adminSchool->pluck('school_id')->toArray();
        if ($this->school_id && !in_array($this->school_id, $schoolIds)) {
            $schoolIds[] = $this->school_id;
        }
        return $schoolIds;
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

    /**
     * Check if admin has any cashier role (case-insensitive & substring matching)
     */
    public function isKasir(): bool
    {
        $roles = $this->roles->pluck('name')->map(fn($r) => strtolower($r));
        foreach ($roles as $role) {
            if (str_contains($role, 'kasir')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if admin has Kasir Koperasi / Kantin role (case-insensitive & substring matching)
     */
    public function isKasirKoperasi(): bool
    {
        $roles = $this->roles->pluck('name')->map(fn($r) => strtolower($r));
        foreach ($roles as $role) {
            if (str_contains($role, 'kasir') && (str_contains($role, 'koperasi') || str_contains($role, 'kantin'))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if admin has Koordinator Cahaya Mart role (case-insensitive & substring matching)
     */
    public function isKoordinatorCahayaMart(): bool
    {
        $roles = $this->roles->pluck('name')->map(fn($r) => strtolower($r));
        foreach ($roles as $role) {
            if (str_contains($role, 'koordinator') && (str_contains($role, 'cahaya mart') || str_contains($role, 'mart') || str_contains($role, 'koperasi'))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if admin has Kasir Outlet role (case-insensitive & substring matching)
     */
    public function isKasirOutlet(): bool
    {
        $roles = $this->roles->pluck('name')->map(fn($r) => strtolower($r));
        foreach ($roles as $role) {
            if (str_contains($role, 'kasir') && str_contains($role, 'outlet')) {
                return true;
            }
        }
        foreach ($roles as $role) {
            if (str_contains($role, 'kasir') && !str_contains($role, 'koperasi') && !str_contains($role, 'kantin')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the effective outlet ID based on mode and request input.
     *
     * @param string|null $mode
     * @param string|null $requestOutletId
     * @return string|null
     */
    public function getEffectiveOutletId(?string $mode, ?string $requestOutletId = null): ?string
    {
        if ($this->hasRole('Super Admin')) {
            if ($requestOutletId) return $requestOutletId;
            return null;
        }

        $koperasi = \App\Models\Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
        $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

        if ($this->isKasirKoperasi() || $this->isKoordinatorCahayaMart()) {
            return $koperasiId;
        }

        if ($this->isKasirOutlet()) {
            $authOutletIds = $this->getOutletIds();
            $allowedOutletIds = array_diff($authOutletIds, [$koperasiId]);

            if ($requestOutletId && in_array($requestOutletId, $allowedOutletIds)) {
                return $requestOutletId;
            }

            if (!empty($allowedOutletIds)) {
                return $allowedOutletIds[0];
            }

            return null;
        }

        if ($this->outlet_id) {
            return $this->outlet_id;
        }

        if ($mode === 'outlet') {
            $authOutletIds = $this->getOutletIds();
            
            if ($requestOutletId && in_array($requestOutletId, $authOutletIds)) {
                return $requestOutletId;
            }
            
            if (!empty($authOutletIds)) {
                return $authOutletIds[0];
            }
        }

        return null;
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

    /**
     * Override Spatie hasRole to support case-insensitive role name comparisons,
     * array of roles, pipe-delimited strings, and Collection instances.
     *
     * @param string|int|array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection $roles
     * @param string|null $guard
     * @return bool
     */
    public function hasRole($roles, string $guard = null): bool
    {
        $this->loadMissing('roles');

        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = explode('|', $roles);
        }

        if (is_string($roles)) {
            $normalizedTarget = strtolower(trim($roles));
            $collection = $guard ? $this->roles->where('guard_name', $guard) : $this->roles;
            return $collection->contains(function ($item) use ($normalizedTarget) {
                return strtolower(trim($item->name)) === $normalizedTarget;
            });
        }

        if (is_int($roles)) {
            $roleClass = $this->getRoleClass();
            $key = (new $roleClass())->getKeyName();

            return $guard
                ? $this->roles->where('guard_name', $guard)->contains($key, $roles)
                : $this->roles->contains($key, $roles);
        }

        if ($roles instanceof \Spatie\Permission\Contracts\Role) {
            return $this->roles->contains($roles->getKeyName(), $roles->getKey());
        }

        if (is_array($roles) || $roles instanceof \Illuminate\Support\Collection) {
            foreach ($roles as $role) {
                if ($this->hasRole($role, $guard)) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * Cek apakah admin HANYA bertugas sebagai kasir / staff outlet,
     * tanpa memiliki peran administratif backoffice lain (seperti Super Admin, Bendahara, TU, Piket, Asrama, dll.)
     */
    public function isKasirOnly(): bool
    {
        if (!$this->isKasir() && !$this->isKasirOutlet() && !$this->isKasirKoperasi()) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return false;
        }

        $roles = $this->roles->pluck('name')->map(fn($r) => strtolower($r));
        foreach ($roles as $role) {
            // Jika punya peran administratif / backoffice lain selain kasir/outlet murni
            if (
                str_contains($role, 'admin') ||
                str_contains($role, 'bendahara') ||
                str_contains($role, 'piket') ||
                str_contains($role, 'asrama') ||
                str_contains($role, 'tata usaha') ||
                str_contains($role, 'tu ') ||
                str_contains($role, 'yayasan') ||
                str_contains($role, 'kepala sekolah') ||
                str_contains($role, 'koordinator')
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if admin is Super Admin
     */
    public function isSuperAdmin(): bool
    {
        if ($this->role_id == 1) {
            return true;
        }

        if (in_array(strtolower($this->email), ['siswanto@cahayatasbih.or.id', 'arsito@cahayatasbih.or.id', 'superadmin@gmail.com'])) {
            return true;
        }

        if ($this->roles && $this->roles->contains(function ($role) {
            return strtolower(trim($role->name)) === 'super admin';
        })) {
            return true;
        }

        return $this->hasRole('Super Admin');
    }
}


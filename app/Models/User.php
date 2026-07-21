<?php

namespace App\Models;


use App\Traits\HasAvatarUrl;
use App\Traits\UuidTrait;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\LogActivityTrait;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UuidTrait, SoftDeletes, HasAvatarUrl, HasRoles, LogActivityTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'access_scope',
        'avatar',
        'is_active',
        'gender',
        'fcm_token',
        'password',
        'last_login',
        'status',
        'jamaah_status',
        'kta',
        'member_branch',
        'member_group',
        'rejection_note',
    ];

    /**
     * Relation to Officer (Petugas) if this user is a petugas.
     */
    public function officer()
    {
        return $this->hasOne(Officer::class);
    }

    public function adminSchool()
    {
        return $this->hasMany(AdminSchool::class, 'admin_id');
    }

    public function getSchoolIds(): array
    {
        $schoolIds = $this->adminSchool ? $this->adminSchool->pluck('school_id')->toArray() : [];
        if (isset($this->school_id) && $this->school_id && !in_array($this->school_id, $schoolIds)) {
            $schoolIds[] = $this->school_id;
        }
        return $schoolIds;
    }

    public function adminOutlet()
    {
        return $this->hasMany(AdminOutlet::class, 'admin_id');
    }

    public function getOutletIds(): array
    {
        $outletIds = $this->adminOutlet ? $this->adminOutlet->pluck('outlet_id')->toArray() : [];
        if (isset($this->outlet_id) && $this->outlet_id && !in_array($this->outlet_id, $outletIds)) {
            $outletIds[] = $this->outlet_id;
        }
        return $outletIds;
    }

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

    public function getEffectiveOutletId(?string $mode, ?string $requestOutletId = null): ?string
    {
        $koperasi = \App\Models\Outlet::where('name', 'Koperasi')->orWhere('code', 'KPR')->first();
        $koperasiId = $koperasi ? $koperasi->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

        if ($this->isKasirKoperasi()) {
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

            $firstNonKoperasi = \App\Models\Outlet::where('id', '!=', $koperasiId)->where('is_active', 1)->orderBy('name')->first();
            return $firstNonKoperasi ? $firstNonKoperasi->id : null;
        }

        if (isset($this->outlet_id) && $this->outlet_id) {
            return $this->outlet_id;
        }

        if ($mode === 'outlet') {
            if ($requestOutletId) {
                return $requestOutletId;
            }

            $authOutletIds = $this->getOutletIds();
            $query = \App\Models\Outlet::where('is_active', 1);

            if (!empty($authOutletIds)) {
                $query->whereIn('id', $authOutletIds);
            } elseif (!$this->hasRole('Super Admin')) {
                if (isset($this->outlet_id) && $this->outlet_id) {
                    $query->where('id', $this->outlet_id);
                }
            }

            $firstOutlet = $query->orderBy('name')->first();
            return $firstOutlet ? $firstOutlet->id : null;
        }

        return $koperasiId;
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login' => 'datetime',
        'password' => 'hashed',
    ];

    protected $appends = [
        'avatar_url',
    ];

    public function student()
    {
        return $this->hasMany(Student::class);
    }

    public function scopeHasSchool($query)
    {
        $admin = Auth::user();
        if ($admin?->hasRole('Super Admin')) {
            return;
        }

        $schoolIds = $admin ? (method_exists($admin, 'getSchoolIds') ? $admin->getSchoolIds() : ($admin->adminSchool ? $admin->adminSchool->pluck('school_id')->toArray() : [])) : [];

        $query->whereHas('student', function ($query) use ($schoolIds) {
            $query->whereHas('classroom', function ($query) use ($schoolIds) {
                $query->whereHas('school', function ($query) use ($schoolIds) {
                    $query->whereIn('id', $schoolIds);
                });
            });
        });
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
     * Check if there's a potential duplicate entry in the database.
     * Normalized phone and name similarity are used as indicators.
     */
    public static function checkDoubleEntry($name, $phone)
    {
        if (empty($phone) || $phone === '-') {
            return null;
        }

        // Normalize phone number
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 2) === '62') {
            $phone = '0' . substr($phone, 2);
        }
        if (substr($phone, 0, 1) !== '0' && strlen($phone) > 0) {
            $phone = '0' . $phone;
        }

        // Find users with the same phone number (normalized)
        $existingUsers = self::where(function ($query) use ($phone) {
            // Check exact phone match
            $query->where('phone', $phone)
                ->orWhere(DB::raw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+62', '0')"), $phone);
        })->get();

        foreach ($existingUsers as $existingUser) {
            if (self::isNameSimilar($name, $existingUser->name)) {
                return $existingUser; // Found duplicate
            }
        }

        return null;
    }

    /**
     * Compare two names for similarity using built-in similar_text
     * and word intersection.
     */
    public static function isNameSimilar($name1, $name2)
    {
        $n1 = strtolower(trim($name1));
        $n2 = strtolower(trim($name2));

        if ($n1 === $n2) {
            return true;
        }

        // 1. similar_text percentage
        similar_text($n1, $n2, $percent);
        if ($percent >= 80) {
            return true;
        }

        // 2. Word intersection (if >= 75% of the words are shared)
        $words1 = array_filter(explode(' ', $n1));
        $words2 = array_filter(explode(' ', $n2));
        
        if (empty($words1) || empty($words2)) {
            return false;
        }

        $intersect = array_intersect($words1, $words2);
        $minWords = min(count($words1), count($words2));
        if ($minWords > 0 && (count($intersect) / $minWords) >= 0.75) {
            return true;
        }

        return false;
    }
}


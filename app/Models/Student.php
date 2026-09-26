<?php

namespace App\Models;

use App\Traits\HasAvatarUrl;
use App\Traits\UuidTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogActivityTrait;

class Student extends Model
{
    const STATUS_ACTIVE = "ACTIVE";
    const STATUS_INACTIVE = "INACTIVE";
    const STATUS_GRADUATED = "GRADUATED";
    const STATUS_TRANSFERRED = "TRANSFERRED";
    const STATUS_DROPPED_OUT = "DROPPED_OUT";
    use HasFactory, SoftDeletes, HasAvatarUrl, LogActivityTrait;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nisn',
        'nis',
        'user_id',
        'asrama_id',
        'asrama_host_id',
        'asrama_name',
        'school_id',
        'classroom_id',
        'name',
        'nickname',
        'born_place',
        'birth_date',
        'gender',
        'saldo',
        'avatar',
        'barcode',
        'is_blocked',
        'daily_limit',
        'saving',
        'status',
        'address',
        'city',
        'province',
    ];

    protected $casts = [
        'daily_limit' => 'integer',
    ];

    /**
     * Mutator: Pastikan saldo tidak pernah disimpan di bawah 0.
     * Ini adalah guard terakhir (last-line defense) agar nilai saldo
     * tidak pernah negatif meskipun ada bug di layer atas.
     */
    public function setSaldoAttribute($value): void
    {
        $this->attributes['saldo'] = max(0, (int) $value);
    }

    protected $appends = [
        'translated_status',
        'avatar_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function asrama()
    {
        return $this->belongsTo(Asrama::class, 'asrama_id');
    }

    public function asramaHost()
    {
        return $this->belongsTo(Admin::class, 'asrama_host_id');
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
        return $this->hasMany(Tahfidz::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function saldoHistories()
    {
        return $this->hasMany(SaldoHistory::class);
    }

    public function latestSaldoHistory()
    {
        return $this->hasOne(SaldoHistory::class)->latestOfMany('created_at');
    }

    public function pointOfSaleTransactions()
    {
        return $this->hasMany(PointOfSaleTransaction::class);
    }

    public function savingHistories()
    {
        return $this->hasMany(SavingHistory::class);
    }

    public function studentBillNotifications()
    {
        return $this->hasMany(StudentBillNotification::class)->latest();
    }

    public function scopeHasSchoolPlace($query)
    {
        $admin = Auth::guard('web')->user() ?? Auth::user();
        if (!$admin || (method_exists($admin, 'isSuperAdmin') && $admin->isSuperAdmin())) {
            return;
        }

        $schoolIds = method_exists($admin, 'getSchoolIds') ? $admin->getSchoolIds() : ($admin->school_id ? [$admin->school_id] : []);
        if (!empty($schoolIds)) {
            return $query->where(function ($q) use ($schoolIds) {
                $q->whereIn('school_id', $schoolIds)
                  ->orWhereHas('classroom', function ($cQ) use ($schoolIds) {
                      $cQ->whereIn('school_id', $schoolIds);
                  });
            });
        }
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Clean up UNPAID bills for periods after student departure/graduation.
     * Retains unpaid bills charged while active as historical arrears.
     */
    public function cleanupFutureUnpaidBills($departureDate = null)
    {
        if ($this->status === self::STATUS_ACTIVE) {
            return;
        }

        $departureYear = $departureDate ? (int)date('Y', strtotime($departureDate)) : (int)date('Y');
        $departureMonth = $departureDate ? (int)date('n', strtotime($departureDate)) : (int)date('n');

        $this->bills()
            ->where('paid_amount', 0)
            ->where(function($query) use ($departureYear, $departureMonth) {
                $query->where('year', '>', $departureYear)
                      ->orWhere(function($sub) use ($departureYear, $departureMonth) {
                          $sub->where('year', '=', $departureYear)
                              ->where('month', '>', $departureMonth);
                      });
            })
            ->delete();
    }

    // count total shopping this day
    public function getTotalShoppingTodayAttribute()
    {
        return $this->pointOfSaleTransactions()
            ->whereDate('paid_at', now())
            ->where('status', PointOfSaleTransaction::STATUS_SUCCESS)
            ->sum('pay_amount') ?? 0;
    }

    public function getListStatusAttribute()
    {
        return [
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Tidak Aktif',
            self::STATUS_GRADUATED => 'Lulus',
            self::STATUS_TRANSFERRED => 'Pindah',
            self::STATUS_DROPPED_OUT => 'Keluar',
        ];
    }

    public function getTranslatedStatusAttribute()
    {
        return $this->list_status[$this->status] ?? '-';
    }


    // getAvatarUrlAttribute() is provided by HasAvatarUrl trait

    // on create generate barcode
    public static function boot()
    {
        parent::boot();
        // static::creating(function ($model) {
        //     $model->barcode = Str::random(17);
        // });
        static::creating(function ($model) {
            $model->barcode = self::generateRandomNumber();
        });

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        // Automatically synchronize flat columns for PWA pages when asrama_id changes
        static::saving(function ($student) {
            if ($student->asrama_id) {
                $asrama = Asrama::find($student->asrama_id);
                if ($asrama) {
                    $student->asrama_name = $asrama->name;
                    $student->asrama_host_id = $asrama->host_admin_id;
                }
            } else {
                // If asrama_id is unset, clear flat columns too
                $student->asrama_name = null;
                $student->asrama_host_id = null;
            }
        });

        static::deleted(function ($student) {
            $student->bills()->where('paid_amount', 0)->delete();
        });
    }

    private static function generateRandomNumber()
    {
        return substr(str_shuffle(str_repeat('0123456789', 17)), 0, 17);
    }

    public function scopeHasSchool($query)
    {
        $admin = Auth::guard('web')->user() ?? Auth::user();
        if (!$admin) {
            return;
        }

        $isSuperOrAdmin = false;
        try {
            if ((method_exists($admin, 'isSuperAdmin') && $admin->isSuperAdmin()) || (method_exists($admin, 'hasRole') && ($admin->hasRole('Super Admin') || $admin->hasRole('Admin')))) {
                $isSuperOrAdmin = true;
            }
        } catch (\Throwable $e) {}

        if ($isSuperOrAdmin) {
            return;
        }

        $schoolIds = method_exists($admin, 'getSchoolIds') ? $admin->getSchoolIds() : ($admin->adminSchool ? $admin->adminSchool->pluck('school_id')->toArray() : []);

        if (empty($schoolIds)) {
            return;
        }

        $query->where(function ($q) use ($schoolIds) {
            $q->whereIn('school_id', $schoolIds)
              ->orWhereHas('classroom', function ($cQ) use ($schoolIds) {
                  $cQ->whereIn('school_id', $schoolIds);
              });
        });
    }

    public function cardPrints()
    {
        return $this->hasMany(StudentCardPrint::class)->orderBy('printed_at', 'desc');
    }

    public function classroomHistories()
    {
        return $this->hasMany(StudentClassroomHistory::class);
    }

    /**
     * Tiered Resolution Engine to get student's classroom for a specific Academic Year
     */
    public function getClassroomForAcademicYear($academicYearId = null)
    {
        if (!$academicYearId) {
            return $this->classroom;
        }

        // Tier 1: Check tb_bills generated for this student in this academic year (excluding PONDOK)
        if ($this->relationLoaded('bills')) {
            $billsInAy = $this->bills
                ->where('academic_year_id', $academicYearId)
                ->filter(fn($b) => !empty($b->classroom_id) && $b->classroom && strtoupper($b->classroom->name) !== 'PONDOK');
        } else {
            $billsInAy = $this->bills()
                ->where('academic_year_id', $academicYearId)
                ->whereNotNull('classroom_id')
                ->whereHas('classroom', function ($q) {
                    $q->where('name', '!=', 'PONDOK');
                })
                ->with('classroom.school')
                ->get();
        }

        if ($billsInAy->count() > 0) {
            $mostFrequentClassId = $billsInAy->groupBy('classroom_id')
                ->sortByDesc(fn($group) => $group->count())
                ->keys()
                ->first();

            $billClass = $billsInAy->firstWhere('classroom_id', $mostFrequentClassId)?->classroom;
            if ($billClass) {
                return $billClass;
            }
        }

        // Tier 2: Check StudentClassroomHistory (excluding PONDOK if possible)
        if ($this->relationLoaded('classroomHistories')) {
            $history = $this->classroomHistories
                ->where('academic_year_id', $academicYearId)
                ->filter(fn($h) => $h->classroom && strtoupper($h->classroom->name) !== 'PONDOK')
                ->first();

            if ($history && $history->classroom) {
                return $history->classroom;
            }
        } elseif ($this->classroomHistories()->where('academic_year_id', $academicYearId)->exists()) {
            $history = $this->classroomHistories()
                ->where('academic_year_id', $academicYearId)
                ->with('classroom.school')
                ->get()
                ->filter(fn($h) => $h->classroom && strtoupper($h->classroom->name) !== 'PONDOK')
                ->first();

            if ($history && $history->classroom) {
                return $history->classroom;
            }
        }

        // Tier 3: Any bill in tb_bills for this academic year
        if ($this->relationLoaded('bills')) {
            $anyBillWithClass = $this->bills
                ->where('academic_year_id', $academicYearId)
                ->first(fn($b) => !empty($b->classroom_id) && $b->classroom);
        } else {
            $anyBillWithClass = $this->bills()
                ->where('academic_year_id', $academicYearId)
                ->whereNotNull('classroom_id')
                ->with('classroom.school')
                ->first();
        }

        if ($anyBillWithClass && $anyBillWithClass->classroom) {
            return $anyBillWithClass->classroom;
        }

        // Tier 4: Fallback to current classroom
        return $this->classroom;
    }

    /**
     * Resolve precise Rombel / Class and UPT / School for a specific Bill or BillType
     */
    public function resolveBillRombelAndSchool($bill)
    {
        $billName = strtoupper($bill->name ?? '');
        $isPondokBill = str_contains($billName, 'PONDOK') 
                        || str_contains($billName, 'ZARKASI') 
                        || str_contains($billName, 'SANTRI');

        $ayId = $bill->academic_year_id ?? $bill->academicYear?->id;

        if ($isPondokBill) {
            $asrama = $this->asrama_name ?? $this->asrama?->name;
            $rombelText = 'Pondok' . ($asrama && strtoupper($asrama) !== 'PONDOK' ? " ({$asrama})" : '');
            
            return [
                'class_name' => $rombelText,
                'school_name' => 'PPTQ CAHAYA TASBIH',
                'is_pondok' => true
            ];
        }

        // Formal Bill (MA / SMP): Resolve student's unified formal classroom for this Academic Year
        $billClass = $this->getClassroomForAcademicYear($ayId);

        $className = $billClass?->name ?? '-';
        $schoolName = $billClass?->school?->name ?? ($this->classroom?->school?->name ?? '');

        return [
            'class_name' => 'Kelas ' . $className,
            'school_name' => $schoolName,
            'is_pondok' => false
        ];
    }

    public function isAlumniSmpMa(): bool
    {
        if ($this->classroom && $this->classroom->school && str_contains(strtoupper($this->classroom->school->name), 'MA')) {
            return \App\Models\StudentClassroomHistory::where('student_id', $this->id)
                ->whereHas('classroom.school', function($q) {
                    $q->where('name', 'like', '%SMP%');
                })->exists();
        }
        return false;
    }

    public function translatedStatus(): string
    {
        return match ($this->status) {
            'ACTIVE' => 'Aktif',
            'INACTIVE' => 'Tidak Aktif',
            'GRADUATED' => 'Lulus',
            'TRANSFERRED' => 'Pindah',
            'DROPPED_OUT' => 'Keluar',
            default => 'Tidak Diketahui',
        };
    }

    public function attendances()
    {
        return $this->morphMany(Attendance::class, 'presensiable');
    }

    public function biometricMappings()
    {
        return $this->morphMany(BiometricMapping::class, 'presensiable');
    }

    /**
     * Get the calendar year when the student entered the school.
     * Uses NIS as primary source of truth, falls back to first classroom history,
     * and then to created_at year.
     */
    public function getEntryYear(): int
    {
        // 1. Try to extract entry year from NIS (format: [4-digit pondok][2-digit year][3-digit sequence])
        if ($this->nis) {
            $cleanNis = preg_replace('/\D/', '', $this->nis);
            if (strlen($cleanNis) == 9) {
                $yearPart = substr($cleanNis, 4, 2);
                if (is_numeric($yearPart)) {
                    $nisYear = 2000 + intval($yearPart);
                    $maxValidYear = (int) date('Y') + 1;
                    if ($nisYear >= 2010 && $nisYear <= $maxValidYear) {
                        return $nisYear;
                    }
                }
            }
        }

        // 2. Try to get entry year from classroom history
        if ($this->relationLoaded('classroomHistories')) {
            $firstHistory = $this->classroomHistories
                ->filter(fn($h) => $h->academicYear !== null)
                ->sortBy(fn($h) => $h->academicYear?->start_year ?? 9999)
                ->first();
        } else {
            $firstHistory = $this->classroomHistories()
                ->whereHas('academicYear')
                ->with('academicYear')
                ->join('academic_years', 'student_classroom_histories.academic_year_id', '=', 'academic_years.id')
                ->orderBy('academic_years.start_year', 'asc')
                ->select('student_classroom_histories.*')
                ->first();
        }

        if ($firstHistory && $firstHistory->academicYear) {
            $historyStartYear = $firstHistory->academicYear->getStartYearSafe();
            if ($historyStartYear) {
                return $historyStartYear;
            }
        }

        // 3. Fallback to created_at year
        return $this->created_at ? $this->created_at->year : intval(date('Y'));
    }

    /**
     * Determine if PWA login is allowed for this student
     */
    public function isPwaLoginAllowed(): bool
    {
        $appSetting = \App\Models\ApplicationSetting::first();
        if ($appSetting && isset($appSetting->allow_pwa_login_wali) && !$appSetting->allow_pwa_login_wali) {
            return false;
        }

        if ($this->classroom && $this->classroom->allow_pwa_login !== null) {
            return (bool) $this->classroom->allow_pwa_login;
        }

        if ($this->school && $this->school->allow_pwa_login !== null) {
            return (bool) $this->school->allow_pwa_login;
        }

        return true;
    }

    /**
     * Determine if PWA Saldo (Hero Card & Riwayat Saldo) should be displayed for this student
     */
    public function isPwaSaldoVisible(): bool
    {
        if ($this->classroom && $this->classroom->show_pwa_saldo !== null) {
            return (bool) $this->classroom->show_pwa_saldo;
        }

        if ($this->school && $this->school->show_pwa_saldo !== null) {
            return (bool) $this->school->show_pwa_saldo;
        }

        return true;
    }

    /**
     * Determine if PWA Saldo Payment is allowed for this student
     */
    public function isPwaSaldoPaymentAllowed(): bool
    {
        $appSetting = \App\Models\ApplicationSetting::first();
        if ($appSetting && isset($appSetting->allow_pwa_saldo_payment_wali) && !$appSetting->allow_pwa_saldo_payment_wali) {
            return false;
        }

        if ($this->classroom && $this->classroom->allow_pwa_saldo_payment !== null) {
            return (bool) $this->classroom->allow_pwa_saldo_payment;
        }

        if ($this->school && $this->school->allow_pwa_saldo_payment !== null) {
            return (bool) $this->school->allow_pwa_saldo_payment;
        }

        return true;
    }

    /**
     * Get consolidated PWA permissions payload
     */
    public function getPwaPermissions(): array
    {
        return [
            'allow_pwa_login' => $this->isPwaLoginAllowed(),
            'show_pwa_saldo' => $this->isPwaSaldoVisible(),
            'allow_pwa_saldo_payment' => $this->isPwaSaldoPaymentAllowed(),
            'classroom_id' => $this->classroom_id,
            'classroom_name' => $this->classroom?->name,
            'school_id' => $this->school_id,
            'school_name' => $this->school?->name,
        ];
    }

    /**
     * Get the effective daily limit based on Admin settings (Classroom > School) or Wali's setting
     */
    public function getEffectiveDailyLimit(): ?int
    {
        // 1. Prioritas Pertama: Cek setting limit kustom dari Wali Santri (jika -1 atau > 0)
        if ($this->daily_limit == -1) {
            return 0; // Wali secara eksplisit menonaktifkan limit (No Limit)
        }
        
        if ($this->daily_limit > 0) {
            return (int) $this->daily_limit;
        }

        // 2. Prioritas Kedua (Fallback): Cek setting dari Kelas
        if ($this->classroom && $this->classroom->is_saldo_limit_active) {
            return (int) $this->classroom->saldo_limit;
        }

        // 3. Prioritas Ketiga (Fallback): Cek setting dari Sekolah
        $school = $this->school ?? ($this->classroom ? $this->classroom->school : null);
        if ($school && $school->is_saldo_limit_active) {
            return (int) $school->saldo_limit;
        }

        return 0;
    }

    /**
     * Check if Admin has enforced a saldo limit
     */
    public function hasAdminSaldoLimitActive(): bool
    {
        if ($this->classroom && $this->classroom->is_saldo_limit_active) {
            return true;
        }

        $school = $this->school ?? ($this->classroom ? $this->classroom->school : null);
        if ($school && $school->is_saldo_limit_active) {
            return true;
        }

        return false;
    }
}


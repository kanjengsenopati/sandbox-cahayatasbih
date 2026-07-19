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
        // if auth user have school_id, then use it
        if (Auth::guard('web')->user()->school_id) {
            return $query->whereSchoolId(Auth::guard('web')->user()->school_id);
        }
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
            $student->bills()->where('status', \App\Models\Bill::STATUS_UNPAID)->delete();
        });
    }

    private static function generateRandomNumber()
    {
        return substr(str_shuffle(str_repeat('0123456789', 17)), 0, 17);
    }

    public function scopeHasSchool($query)
    {
        $admin = Auth::user();
        if ($admin?->hasRole('Super Admin')) {
            return;
        }

        $schoolIds = $admin ? (method_exists($admin, 'getSchoolIds') ? $admin->getSchoolIds() : ($admin->adminSchool ? $admin->adminSchool->pluck('school_id')->toArray() : [])) : [];

        $query->whereHas('classroom', function ($query) use ($schoolIds) {
            $query->whereHas('school', function ($query) use ($schoolIds) {
                $query->whereIn('id', $schoolIds);
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
                    return 2000 + intval($yearPart);
                }
            }
        }

        // 2. Try to get entry year from classroom history
        $firstHistory = $this->classroomHistories()
            ->with('academicYear')
            ->get()
            ->sortBy(function ($history) {
                return $history->academicYear?->start_year ?? 9999;
            })
            ->first();

        if ($firstHistory && $firstHistory->academicYear) {
            $historyStartYear = $firstHistory->academicYear->getStartYearSafe();
            if ($historyStartYear) {
                return $historyStartYear;
            }
        }

        // 3. Fallback to created_at year
        return $this->created_at ? $this->created_at->year : intval(date('Y'));
    }
}


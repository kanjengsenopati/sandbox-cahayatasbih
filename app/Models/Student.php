<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    const STATUS_ACTIVE = "ACTIVE";
    const STATUS_INACTIVE = "INACTIVE";
    const STATUS_GRADUATED = "GRADUATED";
    const STATUS_TRANSFERRED = "TRANSFERRED";
    const STATUS_DROPPED_OUT = "DROPPED_OUT";
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nisn',
        'nis',
        'user_id',
        'school_id',
        'classroom_id',
        'name',
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
    ];

    protected $casts = [
        'daily_limit' => 'integer',
    ];

    protected $appends = [
        'translated_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
        return $this->hasMany(StudentBillNotification::class);
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
    }

    private static function generateRandomNumber()
    {
        return substr(str_shuffle(str_repeat('0123456789', 17)), 0, 17);
    }

    public function scopeHasSchool($query)
    {
        // Assuming 'adminSchool' is a relationship returning the school IDs the admin can access
        $admin = Auth::user();
        $schoolIds = $admin?->adminSchool?->pluck('school_id');
        $query->whereHas('classroom', function ($query) use ($schoolIds) {
            $query->whereHas('school', function ($query) use ($schoolIds) {
                $query->whereIn('id', $schoolIds);
            });
        });
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
}

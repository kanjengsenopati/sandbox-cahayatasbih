<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class School extends Model
{
    const TYPE_SMP = 'SMP';
    const TYPE_MA = 'MA';
    const TYPE_PONDOK = 'PONDOK';
    const TYPE_DEMO = 'DEMO';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'slug',
        'address',
        'description',
        'features',
        'icon_name',
    ];

    public static function getListType()
    {
        return [
            self::TYPE_SMP => 'SMP',
            self::TYPE_MA => 'MA',
            self::TYPE_PONDOK => 'PONDOK',
            self::TYPE_DEMO => 'DEMO',
        ];
    }

    public function admin()
    {
        return $this->hasMany(Admin::class)->withTrashed();
    }

    public function classroom()
    {
        return $this->hasMany(Classroom::class);
    }

    public function topupBank()
    {
        return $this->hasMany(TopupBank::class);
    }

    public function saldoBank()
    {
        return $this->topupBank()->where('type', TopupBank::TYPE_SALDO);
    }

    public function savingBank()
    {
        return $this->topupBank()->where('type', TopupBank::TYPE_SAVING);
    }

    public function scopeHasSchool($query)
    {
        // Assuming 'adminSchool' is a relationship returning the school IDs the admin can access
        $admin = Auth::user();
        $schoolIds = $admin?->adminSchool?->pluck('school_id');
        $query->whereIn('id', $schoolIds);
    }
}

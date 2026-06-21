<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UuidTrait;

class Karyawan extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'kamar',
        'jabatan',
        'outlet_id',
        'section',
        'gaji_bulan',
        'gaji_hari',
        'hari_kerja'
    ];

    protected $casts = [
        'gaji_bulan' => 'decimal:2',
        'gaji_hari' => 'decimal:2',
        'hari_kerja' => 'integer'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'outlet_id');
    }
}

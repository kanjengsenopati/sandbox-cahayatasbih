<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Information extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'information_category_id',
        'title',
        'content',
        'image',
        'is_active',
    ];

    protected static function booted()
    {
        static::saved(function ($information) {
            \Illuminate\Support\Facades\Cache::forget('wali_dashboard_informations');
        });

        static::deleted(function ($information) {
            \Illuminate\Support\Facades\Cache::forget('wali_dashboard_informations');
        });
    }

    public function informationCategory()
    {
        return $this->belongsTo(InformationCategory::class);
    }
}

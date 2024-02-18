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

    public function informationCategory()
    {
        return $this->belongsTo(InformationCategory::class);
    }
}

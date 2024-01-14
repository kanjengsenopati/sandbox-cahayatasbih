<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'category_item_id',
        'code',
        'name',
        'image',
        'price',
        'stock',
        'is_active',
    ];

    public function categoryItem()
    {
        return $this->belongsTo(CategoryItem::class);
    }
}

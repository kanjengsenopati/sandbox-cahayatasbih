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
        'selling_price',
        'profit',
        'stock',
        'is_active',
    ];

    // protected $appends = [
    //     'total_selling'
    // ];

    public function getTotalSellingAttribute()
    {
        return PointOfSaleTransactionDetail::where('item_id', $this->id)->sum('quantity');
    }

    public function categoryItem()
    {
        return $this->belongsTo(CategoryItem::class)->withTrashed();
    }

    public function stockHistories()
    {
        return $this->hasMany(StockHistory::class);
    }

    public function pointOfSaleTransactionDetails()
    {
        return $this->hasMany(PointOfSaleTransactionDetail::class)->withTrashed();
    }
}

<?php

namespace App\Models;

use App\Models\Item;
use App\Traits\UuidTrait;
use App\Models\PointOfSaleTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PointOfSaleTransactionDetail extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'point_of_sales_transaction_id',
        'item_id',
        'quantity',
        'price',
        'total',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function pointOfSaleTransaction()
    {
        return $this->belongsTo(PointOfSaleTransaction::class);
    }
}

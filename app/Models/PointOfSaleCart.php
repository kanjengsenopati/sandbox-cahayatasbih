<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PointOfSaleCart extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'admin_id',
        'item_id',
        'quantity',
        'price',
        'total'
    ];


    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}

<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockHistory extends Model
{
    const TYPE_IN = 'IN';
    const TYPE_OUT = 'OUT';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'item_id',
        'admin_id',
        'quantity',
        'type',
    ];


    public function item()
    {
        return $this->belongsTo(Item::class)->withTrashed();
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}

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
    const TYPE_ADJUSTMENT = 'ADJUSTMENT';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'item_id',
        'outlet_id',
        'admin_id',
        'quantity',
        'type',
        'notes',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }


    public function item()
    {
        return $this->belongsTo(Item::class)->withTrashed();
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}

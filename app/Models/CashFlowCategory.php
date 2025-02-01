<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashFlowCategory extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = ['name', 'description'];

    public function cashflow()
    {
        return $this->hasMany(CashFlow::class);
    }
}

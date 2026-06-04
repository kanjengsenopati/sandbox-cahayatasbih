<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutletHandover extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'amount',
        'handover_date',
        'recipient_name',
        'evidence_path',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'handover_date' => 'date',
        'amount' => 'float',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by')->withTrashed();
    }
}

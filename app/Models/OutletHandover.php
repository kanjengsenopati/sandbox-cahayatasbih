<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutletHandover extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    const TYPE_KOPERASI_TO_OUTLET = 'KOPERASI_TO_OUTLET';
    const TYPE_CASHIER_TO_MANAGEMENT = 'CASHIER_TO_MANAGEMENT';

    const STATUS_PENDING = 'PENDING';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_REJECTED = 'REJECTED';

    protected $fillable = [
        'handover_type',
        'outlet_id',
        'recipient_outlet_id',
        'recipient_id',
        'cashier_id',
        'amount',
        'system_amount',
        'discrepancy',
        'status',
        'verified_by',
        'verified_at',
        'handover_date',
        'recipient_name',
        'evidence_path',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'handover_date' => 'date',
        'verified_at' => 'datetime',
        'amount' => 'float',
        'system_amount' => 'float',
        'discrepancy' => 'float',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function recipientOutlet()
    {
        return $this->belongsTo(Outlet::class, 'recipient_outlet_id');
    }

    public function recipient()
    {
        return $this->belongsTo(Admin::class, 'recipient_id')->withTrashed();
    }

    public function cashier()
    {
        return $this->belongsTo(Admin::class, 'cashier_id')->withTrashed();
    }

    public function verifier()
    {
        return $this->belongsTo(Admin::class, 'verified_by')->withTrashed();
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by')->withTrashed();
    }
}

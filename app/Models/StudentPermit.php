<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentPermit extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'student_id',
        'user_id',
        'admin_id',
        'permit_type',
        'reason',
        'planned_exit_date',
        'planned_return_date',
        'actual_exit_date',
        'exit_photo_santri',
        'exit_photo_escort',
        'exit_escort_name',
        'exit_escort_relation',
        'exit_latitude',
        'exit_longitude',
        'actual_return_date',
        'return_photo_santri',
        'return_photo_escort',
        'return_latitude',
        'return_longitude',
        'status',
        'rejection_reason',
        'barcode_token',
    ];

    protected $casts = [
        'planned_exit_date' => 'datetime',
        'planned_return_date' => 'datetime',
        'actual_exit_date' => 'datetime',
        'actual_return_date' => 'datetime',
    ];

    /**
     * Get the student associated with the leave permit.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the parent/user who requested the leave permit.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin/asatidz who approved the leave permit.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SaldoMigrationBatch extends Model
{
    protected $table = 'saldo_migration_batches';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'migration_token',
        'sent_by',
        'sent_at',
        'total_students',
        'total_saldo',
        'total_saving',
        'status',
        'applied_at',
        'applied_by',
        'notes',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'applied_at' => 'datetime',
        'total_students' => 'integer',
        'total_saldo' => 'integer',
        'total_saving' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaldoMigrationItem::class, 'batch_id', 'id');
    }
}

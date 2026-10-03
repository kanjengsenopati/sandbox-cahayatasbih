<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SaldoMigrationItem extends Model
{
    protected $table = 'saldo_migration_items';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'batch_id',
        'student_id',
        'nis',
        'nisn',
        'name',
        'classroom',
        'old_saldo',
        'old_saving',
        'current_local_saldo',
        'diff_saldo',
        'status',
    ];

    protected $casts = [
        'old_saldo' => 'integer',
        'old_saving' => 'integer',
        'current_local_saldo' => 'integer',
        'diff_saldo' => 'integer',
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

    public function batch(): BelongsTo
    {
        return $this->belongsTo(SaldoMigrationBatch::class, 'batch_id', 'id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}

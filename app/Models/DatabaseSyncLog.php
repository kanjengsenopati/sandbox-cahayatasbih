<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatabaseSyncLog extends Model
{
    protected $fillable = [
        'status',
        'started_at',
        'finished_at',
        'duration',
        'report',
        'error',
    ];

    protected $casts = [
        'report' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}

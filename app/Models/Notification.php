<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'title',
        'body',
        'user_id',
        'payload',
        'type',
        'reference_id',
        'is_seen'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

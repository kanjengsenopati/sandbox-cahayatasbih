<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationMenu extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = ['name', 'flag', 'status'];
}

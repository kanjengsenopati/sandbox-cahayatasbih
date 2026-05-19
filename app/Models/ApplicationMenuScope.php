<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationMenuScope extends Model
{
    use HasFactory, UuidTrait;

    protected $fillable = [
        'application_menu_id',
        'school_id',
        'class_level',
    ];

    public function applicationMenu()
    {
        return $this->belongsTo(ApplicationMenu::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}

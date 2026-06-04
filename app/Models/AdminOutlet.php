<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminOutlet extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    // define the table name
    protected $table = 'admin_outlets';
    protected $fillable = [
        'admin_id',
        'outlet_id',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class)->withTrashed();
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class)->withTrashed();
    }
}

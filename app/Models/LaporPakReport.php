<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporPakReport extends Model
{
    use HasFactory;

    protected $table = 'lapor_pak_reports';

    protected $fillable = [
        'student_id',
        'student_name',
        'school',
        'class_name',
        'parent_name',
        'parent_phone',
        'is_parent_updated',
        'kendala',
        'keterangan',
        'admin_note',
        'status',
    ];

    protected $casts = [
        'is_parent_updated' => 'boolean',
    ];
}

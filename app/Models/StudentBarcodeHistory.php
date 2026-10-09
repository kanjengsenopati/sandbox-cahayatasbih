<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentBarcodeHistory extends Model
{
    use HasFactory;

    protected $table = 'student_barcode_histories';

    protected $fillable = [
        'student_id',
        'old_barcode',
        'new_barcode',
        'action_type',
        'admin_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}

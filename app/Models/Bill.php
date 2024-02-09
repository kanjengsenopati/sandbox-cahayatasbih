<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Bill extends Model
{

    const STATUS_UNPAID = 'UNPAID';
    const STATUS_PAID = 'PAID';
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'bill_type_id',
        'student_id',
        'classroom_id',
        'academic_year_id',
        'month',
        'amount',
        'status',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    protected $appends = [
        'translated_month',
    ];

    public function billType()
    {
        return $this->belongsTo(BillType::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function getTranslatedMonthAttribute()
    {
        return Carbon::createFromFormat('m', $this->month)->translatedFormat('F');
    }
}

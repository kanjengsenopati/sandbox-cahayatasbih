<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'name',
        'start_year',
        'end_year',
        'is_active',
    ];

    /**
     * Safely get the starting calendar year.
     * Parses from name (e.g. '2026/2027' -> 2026) if start_year is null.
     */
    public function getStartYearSafe(): ?int
    {
        if ($this->start_year) {
            return (int) $this->start_year;
        }

        if ($this->name) {
            $parts = explode('/', $this->name);
            if (count($parts) > 0 && is_numeric($parts[0])) {
                return intval($parts[0]);
            }
        }

        return null;
    }

    public function billTypes()
    {
        return $this->hasMany(BillType::class);
    }
}

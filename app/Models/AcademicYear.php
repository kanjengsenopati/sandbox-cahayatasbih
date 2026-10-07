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

    /**
     * Safely get the ending calendar year.
     * Parses from name (e.g. '2026/2027' -> 2027) if end_year is null.
     */
    public function getEndYearSafe(): int
    {
        if ($this->end_year) {
            return (int) $this->end_year;
        }

        if ($this->name) {
            $parts = explode('/', $this->name);
            if (count($parts) > 1 && is_numeric($parts[1])) {
                return intval($parts[1]);
            }
        }

        $startYear = $this->getStartYearSafe() ?? (int) date('Y');
        return $startYear + 1;
    }

    /**
     * Get the expected calendar year for a specific month (1-12) in this academic year.
     * Months 7-12 belong to start_year, months 1-6 belong to end_year.
     */
    public function getYearForMonth(int $month): int
    {
        return ($month >= 7) ? ($this->getStartYearSafe() ?? (int) date('Y')) : $this->getEndYearSafe();
    }

    public function billTypes()
    {
        return $this->hasMany(BillType::class);
    }
}

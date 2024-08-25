<?php

namespace App\Models\Traits;

use DateTimeInterface;

trait GeneralTrait
{
    public function scopeFilter($query, $column, $param)
    {
        $query->when($param, function ($query) use ($column, $param) {
            $query->where($column, $param);
        });
    }

    public function scopeSearchLike($query, $column, $param)
    {
        $query->when($param, function ($query) use ($column, $param) {
            $query->where($column, 'ilike', '%' . $param . '%');
        });
    }

    public function scopeFilterRelation($query, $relation, $column, $param)
    {
        $query->when($param, function ($query) use ($relation, $column, $param) {
            $query->whereHas($relation, function ($query) use ($column, $param) {
                $query->where($column, $param);
            });
        });
    }

    public function scopeSearchLikeRelation($query, $relation, $column, $param)
    {
        $query->when($param, function ($query) use ($relation, $column, $param) {
            $query->whereHas($relation, function ($query) use ($column, $param) {
                $query->where($column, 'ilike', '%' . $param . '%');
            });
        });
    }

    public function scopeFilterGreaterThanEqual($query, $column, $param)
    {
        $query->when($param, function ($query) use ($column, $param) {
            $query->whereDate($column, '>=', $param);
        });
    }

    public function scopeFilterLowerThanEqual($query, $column, $param)
    {
        $query->when($param, function ($query) use ($column, $param) {
            $query->whereDate($column, '<=', $param);
        });
    }

    public function scopeDateRangeFilter($query, $column, $startDate, $endDate)
    {
        $query->when($startDate && $endDate, function ($query) use ($column, $startDate, $endDate) {
            $query->whereDate($column, '>=', $startDate)
                ->whereDate($column, '<=', $endDate);
        });
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d-m-Y H:i', strtotime($value));
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d-m-Y H:i');
    }

    public function scopeSchoolFilter($query, $column, $param)
    {
        $query->when($param, function ($query) use ($column, $param) {
            $query->whereHas('student.classroom', function ($query) use ($column, $param) {
                $query->where($column, $param);
            });
        });
    }

    public function scopeClassroomFilter($query, $column, $param)
    {
        $query->when($param, function ($query) use ($column, $param) {
            $query->whereHas('student', function ($query) use ($column, $param) {
                $query->where($column, $param);
            });
        });
    }
}

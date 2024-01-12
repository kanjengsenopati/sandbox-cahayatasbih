<?php

namespace App\Models\Traits;


trait searchTrait
{
    public function scopeSearch($query,$column,$param){
        $query->when($param, function ($query) use ($column,$param) {
            $query->where($column,$param);
         });
    }

    public function scopeSearchLike($query,$column,$param){
        $query->when($param, function ($query) use ($column,$param) {
            $query->where($column, 'ilike', '%' . $param . '%');
         });
    }

    public function scopeSearchRelation($query,$relation,$column,$param){
        $query->when($param, function ($query) use ($relation,$column,$param) {
            $query->whereHas($relation, function($query) use ($column,$param){
                $query->where($column,$param);
            });
         });
    }

    public function scopeSearchLikeRelation($query,$relation,$column,$param){
        $query->when($param, function ($query) use ($relation,$column,$param) {
            $query->whereHas($relation, function($query) use ($column,$param){
                $query->where($column, 'ilike', '%' . $param . '%');
            });
         });
    }

    public function scopeFilterGreaterThanEqual($query,$column,$param){
        $query->when($param, function ($query) use ($column,$param) {
            $query->whereDate($column,'>=',$param);
         });
    }

    public function scopeFilterLowerThanEqual($query,$column,$param){
        $query->when($param, function ($query) use ($column,$param) {
            $query->whereDate($column,'<=',$param);
         });
    }
}

<?php

namespace App\Models\Parfumer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Filter extends Model
{
    use HasFactory;

    protected $table = 'filters_uk';

    public function filterFields() {
        return $this->hasMany(FilterFields::class, 'id_filters');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'categories_filters', 'id_filters', 'id_categories');
    }

    protected static function booted(){

        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('sort', 'ASC');
        });

        static::addGlobalScope('is_show', function (Builder $builder) {
            $builder->where('is_show', 1);
        });
    }
}

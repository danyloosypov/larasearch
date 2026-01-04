<?php

namespace App\Models\Raystop;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories_pl';

    public function products()
    {
        return $this->hasMany(Product::class, 'id_categories');
    }

    protected static function booted()
    {
        static::addGlobalScope('order', function (Builder $builder){
            $builder->orderBy('sort', 'DESC');
        });
    }
}

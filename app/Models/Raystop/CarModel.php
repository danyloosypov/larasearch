<?php

namespace App\Models\Raystop;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    use HasFactory;

    protected $table = 'models';

    protected $fillable = [
        'title',
        'slug',
        'id_brands',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'id_models');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'id_brands');
    }

    protected static function booted()
    {
        static::addGlobalScope('products', function (Builder $builder){
            $builder->whereHas('products');
        });
    }
}

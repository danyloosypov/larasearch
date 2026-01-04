<?php

namespace App\Models\Raystop;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $table = 'brands';

    protected $fillable = [
        'title',
        'slug',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'id_brands');
    }

    public function synonyms()
    {
        return $this->hasMany(BrandSynonym::class, 'id_brands');
    }

    public function models()
    {
        return $this->hasMany(CarModel::class, 'id_brands');
    }

    protected static function booted()
    {
        static::addGlobalScope('products', function (Builder $builder){
            $builder->whereHas('products');
        });
    }
}

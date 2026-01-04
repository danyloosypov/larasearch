<?php

namespace App\Models\Parfumer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Brand extends Model
{
    use HasFactory;

    protected $table = 'brands_uk';

    protected $attributes = [
        'discount'           => 0,
        'is_active'          => 1,
        'meta_title'         => '',
        'meta_description'   => '',
        'meta_keywords'      => '',
        'image'              => '',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'id_brands');
    }

    protected static function booted()
    {
        static::addGlobalScope('is_active', function (Builder $builder) {
            $builder->where('is_active', 1);
        });
    }
}

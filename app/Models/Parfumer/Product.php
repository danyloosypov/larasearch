<?php

namespace App\Models\Parfumer;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products_uk';

    protected $attributes = [
        'composition'           => '',
        'uses'                  => '',
        'title_additional'      => '',
        'old_price'             => 0,
        'price'                 => 0,
        'client_price'          => 0,
        'meta_title'            => '',
        'meta_description'      => '',
        'meta_keywords'         => '',
        'is_show'               => 0,
        'id_brands'             => 0,
        'is_top'                => 0,
        'is_recomended'         => 0,
        'is_new'                => 0,
        'is_new_bottling'       => 0,
        'image'                 => '',
        'image_jpg'             => '',
        'gallery'               => '',
        'id_statuses'           => 0,
        'content'               => '',
        'discount'              => 0,
        'is_attributes_set'     => 0,
        'active_discount_id'    => 0,
        'active_discount_tag'   => '',
        'variation_image'       => '',
        'variation_color'       => '',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'products_categories', 'id_products', 'id_categories');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'id_brands');
    }

    public function characteristics()
    {
        return $this->hasMany(Characteristic::class, 'id_products');
    }

    public function filterFields()
    {
        return $this->belongsToMany(FilterField::class, 'products_filter_fields', 'id_products', 'id_filter_fields');
    }

    public function attributes()
    {
        return $this->belongsToMany(AttributeValue::class, 'products_attributes_values', 'id_products', 'id_attributes_values');
    }

    protected static function booted()
    {
        static::addGlobalScope('is_show', function (Builder $builder) {
            $builder->where('is_show', 1)
                ->where(function ($query) {
                    $query->whereHas('brand', function ($q) {
                        $q->where('is_active', 1);
                    })->orWhereNull('id_brands')
                        ->orWhere('id_brands', 0);
                });
        });
    }
}

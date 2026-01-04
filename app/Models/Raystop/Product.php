<?php

namespace App\Models\Raystop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products_pl';
    protected $fillable = [
        'id',
        'title',
        'slug',
        'id_categories',
        'gallery',
        'is_show',
        'is_show_main',
        'price',
        'old_price',
        'in_stock',
        'content',
        'id_packages',
        'id_brands',
        'id_models',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $attributes = [
        'is_show_main'	        => 0,
        'in_stock'              => 1,
        'gallery'               => '[]',
        'content'               => '',
        'old_price'			    => 0,
        'is_show'			    => 1,
        'meta_title'            => '',
        'meta_description'      => '',
        'meta_keywords'         => '',
        'is_not_show_package_gallery'   => 0,
    ];

    public function getBySlug($slug)
    {

        return $this->where('slug', $slug)
            ->with('category')
            ->with('brand')
            ->with('model')
            ->with('types')
            ->with('package')
            ->first() ?? abort(404);
    }

    public function getItems($request, $params, $pagesize = 8, $page = 1)
    {
        $filter = new ProductFilter($request, $params);

        $query = $this->when(isset($params['category']), function($q) use ($params, $filter) {
            if ($params['category'] == 3) {
                $q->where('id_categories', 3);
            } else {
                $q->filter($filter);
            }
        });

        $count = $query->count();

        $items = $query
            ->skip((intval($page) - 1) * $pagesize)
            ->limit($pagesize)
            ->get();

        return [$count, $items];
    }

    public function getSimilarCategories($idCategory, $product)
    {
        $items = $this->where('id_categories', $idCategory)
            ->where('id_brands', $product->id_brands)
            ->where('id_models', $product->id_models)
            ->limit(4)
            ->get();

        return $items;
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'id_categories');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'id_packages');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'id_brands');
    }

    public function model()
    {
        return $this->belongsTo(CarModel::class, 'id_models');
    }

    public function types()
    {
        return $this->belongsToMany(Type::class, 'products_types', 'id_products', 'id_types');
    }

    public function drives()
    {
        return $this->belongsToMany(Drive::class, 'products_drives', 'id_products', 'id_drives');
    }

    public function engines()
    {
        return $this->belongsToMany(Engine::class, 'products_engines', 'id_products', 'id_engines');
    }

    protected static function booted()
    {
        static::addGlobalScope('is_show', function (Builder $builder) {
            $builder->where('is_show', 1);
        });
    }

    public static function oppositeCategory(?int $categoryId): ?int
    {
        return match ($categoryId) {
            1 => 2,
            2 => 1,
            default => null,
        };
    }
}

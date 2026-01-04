<?php

namespace App\Models\Parfumer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories_uk';

    protected $attributes = [
        'sort'              => 0,
        'image'             => '',
        'meta_title'        => '',
        'meta_description'  => '',
        'meta_keywords'     => '',
        'discount'           => 0,
    ];

    private $descendants = [];

    public function parent() {
        return $this->belongsTo(Category::class, 'id_categories');
    }

    public function hasChildren(){
        if($this->childrenCategories->count()){
            return true;
        }
        return false;
    }

    public function findDescendants(Category $category){
        $this->descendants[] = $category->id;

        if($category->hasChildren()){
            foreach($category->childrenCategories as $child){
                $this->findDescendants($child);
            }
        }
    }

    public function getDescendants(Category $category){
        $this->findDescendants($category);
        return $this->descendants;
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'id_categories');
    }

    public function childrenCategories()
    {
        return $this->categories()->with('childrenCategories');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'products_categories', 'id_categories', 'id_products');
    }

    public function getParentDiscounts()
    {
        $discounts = [];
        $category = $this;

        while ($category->parent) {
            $discounts[] = $category->parent->discount;
            $category = $category->parent;
        }

        return $discounts;
    }

    protected static function booted(){

        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('sort', 'DESC');
        });
    }
}

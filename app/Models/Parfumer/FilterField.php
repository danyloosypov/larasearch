<?php

namespace App\Models\Parfumer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterField extends Model
{
    use HasFactory;

    protected $table = 'filter_fields_uk';

    public function filter() {
        return $this->belongsTo(Filter::class, 'id_filters');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'products_filter_fields', 'id_filter_fields', 'id_products');
    }
}

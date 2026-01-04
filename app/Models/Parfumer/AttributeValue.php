<?php

namespace App\Models\Parfumer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    use HasFactory;

    protected $table = 'attributes_values_uk';

    public function attribute() {
        return $this->belongsTo(Attribute::class, 'id_attributes');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'products_attributes_values', 'id_attributes_values', 'id_products');
    }
}

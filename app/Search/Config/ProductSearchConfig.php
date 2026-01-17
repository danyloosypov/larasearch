<?php

namespace App\Search\Config;

use App\Models\Product;
use App\Search\SearchableConfig;

class ProductSearchConfig implements SearchableConfig
{
    public static function model(): string
    {
        return Product::class;
    }

    public static function queryBy(): string
    {
        return 'name,description,brand,category';
    }

    public static function sortable(): string
    {
        return 'price:asc';
    }

    public static function facetFields(): array
    {
        return ['brand', 'category'];
    }

    public static function arrayFacetFields(): array
    {
        return [];
    }

    public static function rangeFields(): array
    {
        return ['price', 'rating'];
    }
}

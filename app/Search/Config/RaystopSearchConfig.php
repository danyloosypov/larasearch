<?php

namespace App\Search\Config;

use App\Models\Raystop\Product;
use App\Search\SearchableConfig;

class RaystopSearchConfig implements SearchableConfig
{
    public static function model(): string
    {
        return Product::class;
    }

    public static function queryBy(): string
    {
        return 'title,content,brand,category,model,package,types,drives,engines';
    }

    public static function sortable(): string
    {
        return 'price:asc';
    }

    public static function facetFields(): array
    {
        return ['brand', 'category', 'model', 'package'];
    }

    public static function arrayFacetFields(): array
    {
        return ['types', 'drives', 'engines'];
    }

    public static function rangeFields(): array
    {
        return ['price'];
    }
}

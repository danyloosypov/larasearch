<?php

namespace App\Search\Config;

use App\Models\Parfumer\Product;
use App\Search\SearchableConfig;

class ParfumerSearchConfig implements SearchableConfig
{
    public static function model(): string
    {
        return Product::class;
    }

    public static function queryBy(): string
    {
        return 'title,title_additional,sku,content,composition,uses,brand,categories,characteristics,filterFields,attributes';
    }

    public static function sortable(): string
    {
        return 'price:asc';
    }

    public static function facetFields(): array
    {
        return ['brand'];
    }

    public static function arrayFacetFields(): array
    {
        return ['categories', 'characteristics', 'filterFields', 'attributes'];
    }

    public static function rangeFields(): array
    {
        return ['price'];
    }
}

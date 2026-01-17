<?php

namespace App\Search;

interface SearchableConfig
{
    public static function model(): string;
    public static function queryBy(): string;
    public static function sortable(): string;
    public static function facetFields(): array;
    public static function arrayFacetFields(): array;
    public static function rangeFields(): array;
}

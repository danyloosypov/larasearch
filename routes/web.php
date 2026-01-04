<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/search', function (Request $request) {

    $q         = $request->get('q', '');
    $highlight = $request->boolean('highlight', false);

    // Pagination
    $page     = max(1, (int) $request->get('page', 1));
    $perPage  = max(1, (int) $request->get('per_page', 25));
    $offset   = ($page - 1) * $perPage;

    /* -----------------------------
     | Sorting & query_by
     |------------------------------*/
    $sortBy  = $request->get('sort_by', 'price:asc');
    $queryBy = $request->get('query_by', 'name,description,brand,category');

    /* -----------------------------
     | Build filter_by
     |------------------------------*/
    $filters = [];

    $trashed = $request->get('trashed', 'without');
    if ($trashed === 'only') {
        $filters[] = '__soft_deleted:=1';
    } elseif ($trashed === 'without') {
        $filters[] = '__soft_deleted:=0';
    }

    if ($request->has('is_active')) {
        $filters[] = 'is_active:=' . ($request->boolean('is_active') ? 'true' : 'false');
    }

    if ($request->has('is_fk_advantage')) {
        $filters[] = 'is_fk_advantage:=' . ($request->boolean('is_fk_advantage') ? 'true' : 'false');
    }

    if ($request->filled('brand')) {
        $brands = (array) $request->get('brand');
        $filters[] = 'brand:=[' . implode(',', array_map('addslashes', $brands)) . ']';
    }

    if ($request->filled('category')) {
        $categories = (array) $request->get('category');
        $filters[] = 'category:=[' . implode(',', array_map('addslashes', $categories)) . ']';
    }

    if ($request->filled('price_min') || $request->filled('price_max')) {
        $min = $request->get('price_min', '*');
        $max = $request->get('price_max', '*');
        $filters[] = "price:>={$min} && price:<={$max}";
    }

    if ($request->filled('rating_min') || $request->filled('rating_max')) {
        $min = $request->get('rating_min', '*');
        $max = $request->get('rating_max', '*');
        $filters[] = "rating:>={$min} && rating:<={$max}";
    }

    $filterBy = $filters ? implode(' && ', $filters) : null;

    /* -----------------------------
     | Build Typesense options
     |------------------------------*/
    $options = array_filter([
        'query_by'  => $queryBy,
        'sort_by'   => $sortBy,
        'filter_by' => $filterBy,
        'page'      => $page,
        'per_page'  => $perPage,
        'exclude_fields' => 'embedding'
    ]);

    if ($highlight) {
        $options['highlight_full_fields'] = $queryBy;
        $options['highlight_start_tag'] = '<span class="highlight">';
        $options['highlight_end_tag']   = '</span>';
    }

    /* -----------------------------
     | Execute search
     |------------------------------*/
    $raw = Product::search($q)
        ->options($options)
        ->raw(); // raw results from Typesense

    $hits  = $raw['hits'] ?? [];
    $found = $raw['found'] ?? 0;

    /* -----------------------------
     | Map hits to Eloquent models
     |------------------------------*/
    $ids = collect($hits)->pluck('document.id')->all();
    $products = Product::whereIn('uniq_id', $ids)->get()->keyBy('uniq_id');

    $results = collect($hits)->map(function ($hit) use ($products, $highlight) {
        $product = $products[$hit['document']['id']] ?? null;
        if (!$product) return null;

        if ($highlight && isset($hit['highlights'])) {
            foreach ($hit['highlights'] as $hl) {
                $field = $hl['field'];
                if (isset($hl['snippet'])) {
                    $product->setAttribute(
                        $field,
                        str_replace(
                            ['<mark>', '</mark>'],
                            ['<span class="highlight">', '</span>'],
                            $hl['snippet']
                        )
                    );
                }
            }
        }

        return $product;
    })->filter()->values();

    /* -----------------------------
     | Return paginated response
     |------------------------------*/
    return response()->json([
        'data' => $results,
        'meta' => [
            'total'     => $found,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => (int) ceil($found / $perPage),
        ],
    ]);
});


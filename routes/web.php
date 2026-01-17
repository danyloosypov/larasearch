<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Raystop\Product as ProductRaystop;
use App\Models\Parfumer\Product as ProductParfumer;

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

Route::get('/raystop', function (Request $request) {
    $q         = $request->get('q', '');
    $highlight = $request->boolean('highlight', false);

    /* -----------------------------
     | Pagination
     |------------------------------*/
    $page     = max(1, (int) $request->get('page', 1));
    $perPage  = max(1, (int) $request->get('per_page', 20));

    /* -----------------------------
     | Sorting & query_by
     |------------------------------*/
    $sortBy  = $request->get('sort_by', 'price:asc');

    // must match Raystop schema
    $queryBy = $request->get(
        'query_by',
        'title,content,brand,category,model,package,types,drives,engines'
    );

    /* -----------------------------
     | Build filter_by
     |------------------------------*/
    $filters = [];

    // facet filters
    foreach (['brand', 'category', 'model', 'package'] as $field) {
        if ($request->filled($field)) {
            $values = (array) $request->get($field);
            $filters[] = $field . ':=[' . implode(',', array_map('addslashes', $values)) . ']';
        }
    }

    // array facets
    foreach (['types', 'drives', 'engines'] as $field) {
        if ($request->filled($field)) {
            $values = (array) $request->get($field);
            $filters[] = $field . ':=[' . implode(',', array_map('addslashes', $values)) . ']';
        }
    }

    // price range
    if ($request->filled('price_min') || $request->filled('price_max')) {
        $min = $request->get('price_min', '*');
        $max = $request->get('price_max', '*');
        $filters[] = "price:>={$min} && price:<={$max}";
    }

    $filterBy = $filters ? implode(' && ', $filters) : null;

    /* -----------------------------
     | Typesense options
     |------------------------------*/
    $options = array_filter([
        'query_by'  => $queryBy,
        'sort_by'   => $sortBy,
        'filter_by' => $filterBy,
        'page'      => $page,
        'per_page'  => $perPage,
    ]);

    if ($highlight) {
        $options['highlight_full_fields'] = $queryBy;
        $options['highlight_start_tag']   = '<span class="highlight">';
        $options['highlight_end_tag']     = '</span>';
    }

    /* -----------------------------
     | Execute Typesense search
     |------------------------------*/
    $raw = ProductRaystop::search($q)
        ->options($options)
        ->raw();

    $hits  = $raw['hits'] ?? [];
    $found = $raw['found'] ?? 0;

    /* -----------------------------
     | Map to Eloquent models
     |------------------------------*/
    $ids = collect($hits)->pluck('document.id')->all();

    $products = ProductRaystop::whereIn('id', $ids)
        ->get()
        ->keyBy('id');

    $results = collect($hits)->map(function ($hit) use ($products, $highlight) {
        $product = $products[$hit['document']['id']] ?? null;
        if (!$product) {
            return null;
        }

        if ($highlight && isset($hit['highlights'])) {
            foreach ($hit['highlights'] as $hl) {
                if (!empty($hl['snippet'])) {
                    $product->setAttribute($hl['field'], $hl['snippet']);
                }
            }
        }

        return $product;
    })->filter()->values();

    /* -----------------------------
     | Response
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

Route::get('/parfumer', function (Request $request) {
    $q         = $request->get('q', '');
    $highlight = $request->boolean('highlight', false);

    /* -----------------------------
     | Pagination
     |------------------------------*/
    $page     = max(1, (int) $request->get('page', 1));
    $perPage  = max(1, (int) $request->get('per_page', 20));

    /* -----------------------------
     | Sorting & query_by
     |------------------------------*/
    $sortBy  = $request->get('sort_by', 'price:asc');

    // MUST match schema fields
    $queryBy = $request->get(
        'query_by',
        'title,title_additional,sku,content,composition,uses,brand,categories,characteristics,filterFields,attributes'
    );

    /* -----------------------------
     | Build filter_by
     |------------------------------*/
    $filters = [];

    // single-value facet
    if ($request->filled('brand')) {
        $filters[] = 'brand:=' . addslashes($request->get('brand'));
    }

    // array facets
    foreach (['categories', 'characteristics', 'filterFields', 'attributes'] as $field) {
        if ($request->filled($field)) {
            $values = (array) $request->get($field);
            $filters[] = $field . ':=[' . implode(',', array_map('addslashes', $values)) . ']';
        }
    }

    // price range
    if ($request->filled('price_min') || $request->filled('price_max')) {
        $min = $request->get('price_min', '*');
        $max = $request->get('price_max', '*');
        $filters[] = "price:>={$min} && price:<={$max}";
    }

    $filterBy = $filters ? implode(' && ', $filters) : null;

    /* -----------------------------
     | Typesense options
     |------------------------------*/
    $options = array_filter([
        'query_by'  => $queryBy,
        'sort_by'   => $sortBy,
        'filter_by' => $filterBy,
        'page'      => $page,
        'per_page'  => $perPage,
    ]);

    if ($highlight) {
        $options['highlight_full_fields'] = $queryBy;
        $options['highlight_start_tag']   = '<span class="highlight">';
        $options['highlight_end_tag']     = '</span>';
    }

    /* -----------------------------
     | Execute Typesense search
     |------------------------------*/
    $raw = ProductParfumer::search($q)
        ->options($options)
        ->raw();

    $hits  = $raw['hits'] ?? [];
    $found = $raw['found'] ?? 0;

    /* -----------------------------
     | Map hits → Eloquent models
     |------------------------------*/
    $ids = collect($hits)->pluck('document.id')->all();

    $products = ProductParfumer::whereIn('id', $ids)
        ->get()
        ->keyBy('id');

    $results = collect($hits)->map(function ($hit) use ($products, $highlight) {
        $product = $products[$hit['document']['id']] ?? null;
        if (!$product) {
            return null;
        }

        if ($highlight && isset($hit['highlights'])) {
            foreach ($hit['highlights'] as $hl) {
                if (!empty($hl['snippet'])) {
                    $product->setAttribute($hl['field'], $hl['snippet']);
                }
            }
        }

        return $product;
    })->filter()->values();

    /* -----------------------------
     | Response
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

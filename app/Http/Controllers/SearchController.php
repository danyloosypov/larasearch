<?php

namespace App\Http\Controllers;

use App\Search\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request, string $type, SearchService $service) {
        $map = [
            'products' => new \App\Search\Config\ProductSearchConfig(),
            'raystop'  => new \App\Search\Config\RaystopSearchConfig(),
            'parfumer' => new \App\Search\Config\ParfumerSearchConfig(),
        ];

        abort_unless(isset($map[$type]), 404);

        return response()->json(
            $service->search($request, $map[$type])
        );
    }
}

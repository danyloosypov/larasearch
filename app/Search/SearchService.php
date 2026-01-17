<?php

namespace App\Search;

use Illuminate\Http\Request;

class SearchService
{
    public function search(Request $request, SearchableConfig $config): array
    {
        $q         = $request->get('q', '');
        $highlight = $request->boolean('highlight', false);

        $page    = max(1, (int) $request->get('page', 1));
        $perPage = max(1, (int) $request->get('per_page', 20));

        /* -----------------------------
         | Build filters
         |------------------------------*/
        $filters = [];

        foreach ($config::facetFields() as $field) {
            if ($request->filled($field)) {
                $filters[] = "$field:=" . addslashes($request->get($field));
            }
        }

        foreach ($config::arrayFacetFields() as $field) {
            if ($request->filled($field)) {
                $values = (array) $request->get($field);
                $filters[] = "$field:=[" . implode(',', array_map('addslashes', $values)) . "]";
            }
        }

        foreach ($config::rangeFields() as $field) {
            if ($request->filled("{$field}_min") || $request->filled("{$field}_max")) {
                $min = $request->get("{$field}_min", '*');
                $max = $request->get("{$field}_max", '*');
                $filters[] = "$field:>={$min} && $field:<={$max}";
            }
        }

        $options = array_filter([
            'query_by'  => $config::queryBy(),
            'sort_by'   => $request->get('sort_by', $config::sortable()),
            'filter_by' => $filters ? implode(' && ', $filters) : null,
            'page'      => $page,
            'per_page'  => $perPage,
        ]);

        if ($highlight) {
            $options['highlight_full_fields'] = $config::queryBy();
            $options['highlight_start_tag']   = '<span class="highlight">';
            $options['highlight_end_tag']     = '</span>';
        }

        /* -----------------------------
         | Execute search
         |------------------------------*/
        $model = $config::model();

        $raw = $model::search($q)->options($options)->raw();

        return $this->hydrate(
            $model,
            $raw,
            $highlight,
            $page,
            $perPage
        );
    }

    private function hydrate(
        string $model,
        array $raw,
        bool $highlight,
        int $page,
        int $perPage
    ): array {
        $hits  = $raw['hits'] ?? [];
        $found = $raw['found'] ?? 0;

        $ids = collect($hits)->pluck('document.id')->all();

        if ($model == 'App\Models\Product') {
            $models = $model::whereIn('uniq_id', $ids)->get()->keyBy('uniq_id');
        } else {
            $models = $model::whereIn('id', $ids)->get()->keyBy('id');
        }

        $data = collect($hits)->map(function ($hit) use ($models, $highlight) {
            $m = $models[$hit['document']['id']] ?? null;
            if (!$m) return null;

            if ($highlight && isset($hit['highlights'])) {
                foreach ($hit['highlights'] as $hl) {
                    if (!empty($hl['snippet'])) {
                        $m->setAttribute($hl['field'], $hl['snippet']);
                    }
                }
            }

            return $m;
        })->filter()->values();

        return [
            'data' => $data,
            'meta' => [
                'total'     => $found,
                'page'      => $page,
                'per_page'  => $perPage,
                'last_page' => (int) ceil($found / $perPage),
            ],
        ];
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Scout\Scout;

class SmartSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_search_products()
    {
        $response = $this->getJson(route('smart-search', [
            'type' => 'products',
            'q'    => 'phone',
        ]));

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'page', 'per_page', 'last_page'],
            ]);
    }

    public function test_invalid_type_returns_404()
    {
        $response = $this->getJson('/smart-search/unknown');

        $response->assertNotFound();
    }

    public function test_pagination_parameters_are_accepted()
    {
        $response = $this->getJson(route('smart-search', [
            'type'     => 'products',
            'page'     => 2,
            'per_page'=> 10,
        ]));

        $response
            ->assertOk()
            ->assertJsonPath('meta.page', 2)
            ->assertJsonPath('meta.per_page', 10);
    }

    public function test_filters_do_not_break_search()
    {
        $response = $this->getJson(route('smart-search', [
            'type'      => 'products',
            'brand'     => 'Apple',
            'price_min'=> 100,
            'price_max'=> 500,
        ]));

        $response->assertOk();
    }

    public function test_highlight_flag_is_supported()
    {
        $response = $this->getJson(route('smart-search', [
            'type'      => 'products',
            'q'         => 'iphone',
            'highlight' => true,
        ]));

        $response->assertOk();

        $json = $response->json();

        $this->assertNotEmpty($json['data'], 'Search returned no results');

        $hasHighlight = collect($json['data'])
            ->flatten()
            ->contains(fn ($value) =>
                is_string($value) && str_contains($value, 'class="highlight"')
            );

        $this->assertTrue(
            $hasHighlight,
            'Response does not contain highlighted fields'
        );
    }

    public function test_soft_deleted_filter_is_supported()
    {
        $response = $this->getJson(route('smart-search', [
            'type'    => 'products',
            'trashed'=> 'only',
        ]));

        $response->assertOk();
    }



}

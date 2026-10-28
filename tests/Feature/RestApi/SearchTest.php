<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Mockery\MockInterface;
use Webkul\BagistoApi\Tests\Concerns\PreparesSearchData;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Product\Repositories\ElasticSearchRepository;

class SearchTest extends RestApiTestCase
{
    use PreparesSearchData;

    public function test_search_matches_a_synonym(): void
    {
        $this->seedRequiredData();
        $product = $this->productNamed('Zorbex Trail Runner');
        $this->seedSynonyms('glimmerboot,zorbex');

        $response = $this->publicGet('/api/shop/products?query=glimmerboot');

        $response->assertOk();
        expect(array_column($response->json(), 'id'))->toContain($product->id);
    }

    public function test_a_plain_search_is_recorded_as_a_search_term(): void
    {
        $this->seedRequiredData();
        $this->productNamed('Quillmore Lamp');

        $this->publicGet('/api/shop/products?query=quillmore')->assertOk();

        $this->assertDatabaseHas('search_terms', [
            'term' => 'quillmore',
            'results' => 1,
            'channel_id' => core()->getCurrentChannel()->id,
        ]);
    }

    public function test_a_filtered_search_is_not_recorded(): void
    {
        $this->seedRequiredData();

        $this->publicGet('/api/shop/products?query=filteredterm&type=simple')->assertOk();

        $this->assertDatabaseMissing('search_terms', ['term' => 'filteredterm']);
    }

    public function test_search_redirect_returns_the_redirect_url(): void
    {
        $this->seedRequiredData();
        $this->seedSearchTermRedirect('summersale', 'https://shop.example.com/sale');

        $response = $this->publicGet('/api/shop/search-redirect?query=summersale');

        $response->assertOk();
        expect($response->json('0.redirectUrl'))->toBe('https://shop.example.com/sale');
    }

    public function test_search_redirect_is_null_for_a_term_without_one(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/search-redirect?query=no-redirect-here');

        $response->assertOk();
        expect($response->json('0.redirectUrl'))->toBeNull();
    }

    public function test_search_redirect_requires_a_query(): void
    {
        $this->seedRequiredData();

        $this->publicGet('/api/shop/search-redirect')->assertStatus(422);
    }

    public function test_search_redirect_suggestion_is_null_on_the_database_engine(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/search-redirect?query=zorbx');

        $response->assertOk();
        expect($response->json('0'))->toHaveKey('suggestion');
        expect($response->json('0.suggestion'))->toBeNull();
    }

    public function test_suggest_param_is_not_treated_as_a_filter(): void
    {
        $this->seedRequiredData();
        $product = $this->productNamed('Vexlaro Mug');

        $response = $this->publicGet('/api/shop/products?query=vexlaro&suggest=0');

        $response->assertOk();
        expect(array_column($response->json(), 'id'))->toContain($product->id);
        $this->assertDatabaseHas('search_terms', ['term' => 'vexlaro']);
    }

    public function test_elastic_engine_matches_the_ids_elasticsearch_returns(): void
    {
        $this->seedRequiredData();
        $product = $this->productNamed('Plinthor Desk');
        $this->useElasticSearchEngine();

        $this->mock(ElasticSearchRepository::class, function (MockInterface $mock) use ($product) {
            $mock->shouldReceive('getSuggestions')->andReturn(null);
            $mock->shouldReceive('search')->andReturn(['total' => 1, 'ids' => [$product->id]]);
        });

        $response = $this->publicGet('/api/shop/products?query=not-in-the-name');

        $response->assertOk();
        expect(array_column($response->json(), 'id'))->toBe([$product->id]);
    }

    public function test_elastic_engine_searches_with_the_suggestion_and_exposes_it(): void
    {
        $this->seedRequiredData();
        $product = $this->productNamed('Zorbex Trail Runner');
        $this->useElasticSearchEngine();

        $this->mock(ElasticSearchRepository::class, function (MockInterface $mock) use ($product) {
            $mock->shouldReceive('getSuggestions')->with('zorbx')->andReturn('zorbex');
            $mock->shouldReceive('search')->andReturn(['total' => 1, 'ids' => [$product->id]]);
        });

        $redirect = $this->publicGet('/api/shop/search-redirect?query=zorbx');

        $redirect->assertOk();
        expect($redirect->json('0.suggestion'))->toBe('zorbex');

        $this->publicGet('/api/shop/products?query=zorbx')->assertOk();

        $this->assertDatabaseHas('search_terms', ['term' => 'zorbex']);
    }

    public function test_suggest_zero_keeps_the_exact_query_on_the_elastic_engine(): void
    {
        $this->seedRequiredData();
        $this->useElasticSearchEngine();

        $this->mock(ElasticSearchRepository::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('getSuggestions');
            $mock->shouldReceive('search')->andReturn(['total' => 0, 'ids' => []]);
        });

        $redirect = $this->publicGet('/api/shop/search-redirect?query=zorbx&suggest=0');

        $redirect->assertOk();
        expect($redirect->json('0.suggestion'))->toBeNull();

        $response = $this->publicGet('/api/shop/products?query=zorbx&suggest=0');

        $response->assertOk();
        expect($response->json())->toBe([]);
    }

    public function test_elastic_engine_falls_back_to_the_database_when_unreachable(): void
    {
        $this->seedRequiredData();
        $product = $this->productNamed('Kestrovan Lamp');
        $this->useElasticSearchEngine();

        $this->mock(ElasticSearchRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('getSuggestions')->andThrow(new \RuntimeException('No alive nodes'));
            $mock->shouldReceive('search')->andThrow(new \RuntimeException('No alive nodes'));
        });

        $response = $this->publicGet('/api/shop/products?query=kestrovan');

        $response->assertOk();
        expect(array_column($response->json(), 'id'))->toContain($product->id);
    }
}

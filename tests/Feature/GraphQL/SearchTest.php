<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Mockery\MockInterface;
use Webkul\BagistoApi\Tests\Concerns\PreparesSearchData;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Product\Repositories\ElasticSearchRepository;

class SearchTest extends GraphQLTestCase
{
    use PreparesSearchData;

    public function test_search_matches_a_synonym(): void
    {
        $this->seedRequiredData();
        $product = $this->productNamed('Zorbex Trail Runner');
        $this->seedSynonyms('glimmerboot,zorbex');

        $response = $this->graphQL('query search($query: String) { products(query: $query, first: 10) { edges { node { _id } } } }', ['query' => 'glimmerboot']);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect(array_column(array_column($response->json('data.products.edges'), 'node'), '_id'))->toContain($product->id);
        $this->assertDatabaseHas('search_terms', ['term' => 'glimmerboot']);
    }

    public function test_search_redirect_returns_the_redirect_url(): void
    {
        $this->seedRequiredData();
        $this->seedSearchTermRedirect('summersale', 'https://shop.example.com/sale');

        $response = $this->graphQL('{ searchRedirect(query: "summersale") { query redirectUrl } }');

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.searchRedirect.redirectUrl'))->toBe('https://shop.example.com/sale');
    }

    public function test_search_redirect_suggestion_is_null_on_the_database_engine(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL('{ searchRedirect(query: "zorbx") { query redirectUrl suggestion } }');

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.searchRedirect.suggestion'))->toBeNull();
    }

    public function test_elastic_engine_exposes_the_suggestion_and_searches_with_it(): void
    {
        $this->seedRequiredData();
        $product = $this->productNamed('Zorbex Trail Runner');
        $this->useElasticSearchEngine();

        $this->mock(ElasticSearchRepository::class, function (MockInterface $mock) use ($product) {
            $mock->shouldReceive('getSuggestions')->with('zorbx')->andReturn('zorbex');
            $mock->shouldReceive('search')->andReturn(['total' => 1, 'ids' => [$product->id]]);
        });

        $redirect = $this->graphQL('{ searchRedirect(query: "zorbx") { suggestion } }');

        expect($redirect->json('errors'))->toBeNull();
        expect($redirect->json('data.searchRedirect.suggestion'))->toBe('zorbex');

        $response = $this->graphQL('query search($query: String) { products(query: $query, first: 10) { edges { node { _id } } } }', ['query' => 'zorbx']);

        expect($response->json('errors'))->toBeNull();
        expect(array_column(array_column($response->json('data.products.edges'), 'node'), '_id'))->toBe([$product->id]);
        $this->assertDatabaseHas('search_terms', ['term' => 'zorbex']);
    }

    public function test_suggest_false_skips_the_suggestion(): void
    {
        $this->seedRequiredData();
        $this->useElasticSearchEngine();

        $this->mock(ElasticSearchRepository::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('getSuggestions');
            $mock->shouldReceive('search')->andReturn(['total' => 0, 'ids' => []]);
        });

        $response = $this->graphQL('{ products(query: "zorbx", suggest: false, first: 10) { edges { node { _id } } } }');

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.products.edges'))->toBe([]);
    }
}

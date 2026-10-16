<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\Concerns\PreparesSearchData;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

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
}

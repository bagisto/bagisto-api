<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\Concerns\PreparesSearchData;
use Webkul\BagistoApi\Tests\RestApiTestCase;

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
}

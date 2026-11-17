<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class SearchRedirectTest extends RestApiTestCase
{
    /**
     * The redirect URL set on a search term is returned.
     */
    public function test_search_redirect_returns_the_redirect_url(): void
    {
        $this->seedRequiredData();

        $this->seedSearchTermRedirect('summersale', 'https://shop.example.com/sale');

        $response = $this->publicGet('/api/shop/search-redirect?query=summersale');

        $response->assertOk();

        expect($response->json('0.query'))->toBe('summersale')
            ->and($response->json('0.redirectUrl'))->toBe('https://shop.example.com/sale');
    }

    /**
     * A term without a redirect returns null.
     */
    public function test_search_redirect_is_null_for_a_term_without_one(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/search-redirect?query=no-redirect-here');

        $response->assertOk();

        expect($response->json('0.redirectUrl'))->toBeNull()
            ->and($response->json('0'))->toHaveKey('suggestion');
    }

    /**
     * The search term is required.
     */
    public function test_search_redirect_requires_a_query(): void
    {
        $this->seedRequiredData();

        $this->publicGet('/api/shop/search-redirect')->assertStatus(422);
    }

    /**
     * Sending suggest=0 skips the suggestion.
     */
    public function test_suggest_zero_returns_no_suggestion(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/shop/search-redirect?query=zorbx&suggest=0');

        $response->assertOk();

        expect($response->json('0.suggestion'))->toBeNull();
    }

    /**
     * Store a search term with a redirect for the current channel and locale.
     */
    private function seedSearchTermRedirect(string $term, string $redirectUrl): void
    {
        DB::table('search_terms')->insert([
            'term' => $term,
            'results' => 0,
            'uses' => 1,
            'redirect_url' => $redirectUrl,
            'channel_id' => core()->getCurrentChannel()->id,
            'locale' => app()->getLocale(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

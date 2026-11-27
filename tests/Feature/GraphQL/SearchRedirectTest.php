<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class SearchRedirectTest extends GraphQLTestCase
{
    /**
     * The query every test sends.
     */
    private const QUERY = 'query S($query: String!, $suggest: Boolean) { searchRedirect(query: $query, suggest: $suggest) { query redirectUrl suggestion } }';

    /**
     * The redirect URL set on a search term is returned.
     */
    public function test_search_redirect_returns_the_redirect_url(): void
    {
        $this->seedRequiredData();

        DB::table('search_terms')->insert([
            'term' => 'gqlsale',
            'results' => 0,
            'uses' => 1,
            'redirect_url' => 'https://shop.example.com/gql-sale',
            'channel_id' => core()->getCurrentChannel()->id,
            'locale' => app()->getLocale(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->graphQL(self::QUERY, ['query' => 'gqlsale', 'suggest' => false]);

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.searchRedirect.redirectUrl'))->toBe('https://shop.example.com/gql-sale')
            ->and($response->json('data.searchRedirect.suggestion'))->toBeNull();
    }

    /**
     * An empty term is reported in errors.
     */
    public function test_an_empty_query_is_an_error(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL(self::QUERY, ['query' => '']);

        expect($response->json('errors'))->not->toBeNull();
    }
}

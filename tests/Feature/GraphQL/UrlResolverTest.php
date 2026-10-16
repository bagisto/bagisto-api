<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class UrlResolverTest extends GraphQLTestCase
{
    private const QUERY = 'query R($path: String!) { urlResolver(path: $path) { path type entityId targetPath redirectType } }';

    public function test_a_url_rewrite_resolves_to_a_redirect(): void
    {
        $this->seedRequiredData();
        DB::table('url_rewrites')->insert([
            'entity_type' => 'product',
            'request_path' => 'old-gql-'.($suffix = uniqid()),
            'target_path' => 'new-path',
            'redirect_type' => '302',
            'locale' => app()->getLocale(),
        ]);

        $response = $this->graphQL(self::QUERY, ['path' => 'old-gql-'.$suffix]);

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.urlResolver.type'))->toBe('redirect');
        expect($response->json('data.urlResolver.redirectType'))->toBe(302);
    }

    public function test_an_unknown_path_is_an_error(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL(self::QUERY, ['path' => 'nothing-'.uniqid()]);

        expect($response->json('errors'))->not->toBeNull();
    }
}

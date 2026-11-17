<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class UrlResolverTest extends GraphQLTestCase
{
    /**
     * The query every test sends.
     */
    private const QUERY = 'query R($path: String!) { urlResolver(path: $path) { path type entityId targetPath redirectType } }';

    /**
     * A URL rewrite resolves to a redirect with its target and status.
     */
    public function test_a_url_rewrite_resolves_to_a_redirect(): void
    {
        $this->seedRequiredData();

        $requestPath = 'old-gql-'.uniqid();

        DB::table('url_rewrites')->insert([
            'entity_type' => 'product',
            'request_path' => $requestPath,
            'target_path' => 'new-path',
            'redirect_type' => '302',
            'locale' => app()->getLocale(),
        ]);

        $response = $this->graphQL(self::QUERY, ['path' => $requestPath]);

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.urlResolver.type'))->toBe('redirect')
            ->and($response->json('data.urlResolver.targetPath'))->toBe('new-path')
            ->and($response->json('data.urlResolver.redirectType'))->toBe(302);
    }

    /**
     * A path nothing matches is reported in errors.
     */
    public function test_an_unknown_path_is_an_error(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL(self::QUERY, ['path' => 'nothing-'.uniqid()]);

        expect($response->json('errors'))->not->toBeNull();
    }
}

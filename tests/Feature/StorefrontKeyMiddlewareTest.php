<?php

namespace Webkul\BagistoApi\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Http\Middleware\VerifyGraphQLStorefrontKey;
use Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey;
use Webkul\BagistoApi\Models\StorefrontKey;
use Webkul\BagistoApi\Services\ApiKeyService;
use Webkul\BagistoApi\Tests\BagistoApiTestCase;

class StorefrontKeyMiddlewareTest extends BagistoApiTestCase
{
    /**
     * The products query constant.
     */
    private const PRODUCTS_QUERY = '{ products { edges { node { id } } } }';

    /**
     * Test graphql query string on post requires a key.
     */
    public function test_graphql_query_string_on_post_requires_a_key(): void
    {
        $response = $this->runGraphQL($this->graphQLRequest('/api/graphql?query='.urlencode(self::PRODUCTS_QUERY)));

        expect($response->getStatusCode())->toBe(401);
    }

    /**
     * Test graphql get with query requires a key.
     */
    public function test_graphql_get_with_query_requires_a_key(): void
    {
        $request = Request::create('/api/graphql', 'GET', ['query' => self::PRODUCTS_QUERY]);

        expect($this->runGraphQL($request)->getStatusCode())->toBe(401);
    }

    /**
     * Test graphql query selecting typename requires a key.
     */
    public function test_graphql_query_selecting_typename_requires_a_key(): void
    {
        $body = json_encode(['query' => '{ products { edges { node { id __typename } } } }']);

        expect($this->runGraphQL($this->graphQLRequest('/api/graphql', $body))->getStatusCode())->toBe(401);
    }

    /**
     * Test graphql query mixing introspection and data requires a key.
     */
    public function test_graphql_query_mixing_introspection_and_data_requires_a_key(): void
    {
        $body = json_encode(['query' => '{ __schema { queryType { name } } products { edges { node { id } } } }']);

        expect($this->runGraphQL($this->graphQLRequest('/api/graphql', $body))->getStatusCode())->toBe(401);
    }

    /**
     * Test graphql operation named after a public operation requires a key.
     */
    public function test_graphql_operation_named_after_a_public_operation_requires_a_key(): void
    {
        $body = json_encode([
            'query' => 'query __schema { products { edges { node { id } } } }',
            'operationName' => '__schema',
        ]);

        expect($this->runGraphQL($this->graphQLRequest('/api/graphql', $body))->getStatusCode())->toBe(401);
    }

    /**
     * Test graphql raw graphql body requires a key.
     */
    public function test_graphql_raw_graphql_body_requires_a_key(): void
    {
        $request = $this->graphQLRequest('/api/graphql', self::PRODUCTS_QUERY, [], 'application/graphql');

        expect($this->runGraphQL($request)->getStatusCode())->toBe(401);
    }

    /**
     * Test graphql pure introspection is public.
     */
    public function test_graphql_pure_introspection_is_public(): void
    {
        $body = json_encode(['query' => '{ __schema { queryType { name } } __typename }']);

        expect($this->runGraphQL($this->graphQLRequest('/api/graphql', $body))->getStatusCode())->toBe(200);
    }

    /**
     * Test graphql valid key passes.
     */
    public function test_graphql_valid_key_passes(): void
    {
        $key = $this->createKey();
        $body = json_encode(['query' => self::PRODUCTS_QUERY]);

        $response = $this->runGraphQL($this->graphQLRequest('/api/graphql', $body, ['X-STOREFRONT-KEY' => $key]));

        expect($response->getStatusCode())->toBe(200);
    }

    /**
     * Test graphql rate limit is enforced.
     */
    public function test_graphql_rate_limit_is_enforced(): void
    {
        $key = $this->createKey(['rate_limit' => 2]);
        $body = json_encode(['query' => self::PRODUCTS_QUERY]);

        $statuses = [];

        foreach (range(1, 3) as $attempt) {
            $statuses[] = $this->runGraphQL($this->graphQLRequest('/api/graphql', $body, ['X-STOREFRONT-KEY' => $key]))->getStatusCode();
        }

        expect($statuses)->toBe([200, 200, 429]);
    }

    /**
     * Test rest rate limit is per client ip.
     */
    public function test_rest_rate_limit_is_per_client_ip(): void
    {
        $key = $this->createKey(['rate_limit' => 2]);

        expect($this->runRest($key)->getStatusCode())->toBe(200);
        expect($this->runRest($key)->getStatusCode())->toBe(200);
        expect($this->runRest($key)->getStatusCode())->toBe(429);
        expect($this->runRest($key, '198.51.100.20')->getStatusCode())->toBe(200);
    }

    /**
     * Test expired key is rejected.
     */
    public function test_expired_key_is_rejected(): void
    {
        $key = $this->createKey(['expires_at' => now()->subMinute()]);

        expect($this->runRest($key)->getStatusCode())->toBe(403);
    }

    /**
     * Test soft deleted key is rejected.
     */
    public function test_soft_deleted_key_is_rejected(): void
    {
        $key = $this->createKey(['deleted_at' => now()]);

        expect($this->runRest($key)->getStatusCode())->toBe(403);
    }

    /**
     * Test key deleted after validation stops working immediately.
     */
    public function test_key_deleted_after_validation_stops_working_immediately(): void
    {
        $key = $this->createKey();

        expect($this->runRest($key)->getStatusCode())->toBe(200);

        StorefrontKey::where('key', $key)->first()->delete();

        expect($this->runRest($key)->getStatusCode())->toBe(403);
    }

    /**
     * Test cached key is rejected once it expires.
     */
    public function test_cached_key_is_rejected_once_it_expires(): void
    {
        $key = $this->createKey(['expires_at' => now()->addMinutes(10)]);

        expect($this->runRest($key)->getStatusCode())->toBe(200);

        $this->travel(11)->minutes();

        expect(app(ApiKeyService::class)->validate($key, ApiKeyService::KEY_TYPE_SHOP, '203.0.113.10')['valid'])->toBeFalse();
    }

    /**
     * The base case swaps both middlewares for pass-through stubs; these tests need the real ones.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withMiddleware([
            VerifyGraphQLStorefrontKey::class,
            VerifyStorefrontKey::class,
        ]);
    }

    /**
     * Create key.
     */
    private function createKey(array $attributes = []): string
    {
        $key = 'pk_storefront_'.Str::random(32);

        DB::table('storefront_keys')->insert(array_merge([
            'name' => 'Middleware test '.Str::random(12),
            'key' => $key,
            'key_type' => 'shop',
            'is_active' => true,
            'rate_limit' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        return $key;
    }

    /**
     * Graph qlrequest.
     */
    private function graphQLRequest(string $uri, string $body = '', array $headers = [], string $contentType = 'application/json'): Request
    {
        $request = Request::create($uri, 'POST', [], [], [], [
            'CONTENT_TYPE' => $contentType,
            'REMOTE_ADDR' => '203.0.113.10',
        ], $body);

        foreach ($headers as $name => $value) {
            $request->headers->set($name, $value);
        }

        return $request;
    }

    /**
     * Run graph ql.
     */
    private function runGraphQL(Request $request): Response
    {
        return app(VerifyGraphQLStorefrontKey::class)->handle($request, fn () => response()->json(['data' => []]));
    }

    /**
     * Run rest.
     */
    private function runRest(string $key, string $ip = '203.0.113.10'): Response
    {
        $request = Request::create('/api/shop/products', 'GET', [], [], [], ['REMOTE_ADDR' => $ip]);
        $request->headers->set('X-STOREFRONT-KEY', $key);

        return app(VerifyStorefrontKey::class)->handle($request, fn () => response()->json([]));
    }
}

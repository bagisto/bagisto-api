<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Product\Models\Product;

/**
 * GraphQL coverage for the adminProducts query (cursor pagination).
 */
class ProductTest extends AdminApiTestCase
{
    public function test_query_requires_authentication(): void
    {
        $query = <<<'GQL'
            query { adminProducts(first: 1) { edges { node { id } } } }
        GQL;

        $response = $this->adminGraphQL($query);
        // Either explicit auth error or null data with errors.
        $body = $response->json();
        expect($body)->toHaveKey('errors');
    }

    public function test_query_returns_edges_and_pageinfo(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              adminProducts(first: 2) {
                edges { node { id sku type name status price formattedPrice baseImageUrl isSaleable } }
                pageInfo { hasNextPage endCursor }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();

        $data = $response->json('data.adminProducts');
        expect($data)->toBeArray();
        expect($data)->toHaveKeys(['edges', 'pageInfo']);
    }

    public function test_query_filter_by_type(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query($type: String) {
              adminProducts(first: 5, type: $type) {
                edges { node { id type } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['type' => 'simple'], $admin);
        $response->assertOk();

        foreach ($response->json('data.adminProducts.edges') ?? [] as $edge) {
            expect($edge['node']['type'])->toBe('simple');
        }
    }

    public function test_query_search_by_sku(): void
    {
        $admin = $this->createAdmin();

        $product = Product::query()->whereNotNull('sku')->orderBy('id')->first();

        if (! $product) {
            $this->markTestSkipped('No product to search.');
        }

        $query = <<<'GQL'
            query($sku: String) {
              adminProducts(first: 5, sku: $sku) {
                edges { node { id sku } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['sku' => $product->sku], $admin);
        $response->assertOk();

        $edges = $response->json('data.adminProducts.edges') ?? [];
        foreach ($edges as $edge) {
            expect($edge['node']['sku'])->toBe($product->sku);
        }
    }
}

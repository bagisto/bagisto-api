<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;
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

    public function test_filters_are_valid_args_and_camelcase_columns_resolve(): void
    {
        $admin = $this->createAdmin();

        $product = Product::query()
            ->where('type', 'simple')
            ->whereNotNull('sku')
            ->orderByDesc('id')
            ->get()
            ->first(fn ($p) => $p->name !== null);

        if (! $product) {
            $this->markTestSkipped('No named simple product to assert against.');
        }

        $query = <<<'GQL'
            query($sku: String, $type: String) {
              adminProducts(first: 5, sku: $sku, type: $type) {
                edges { node { _id sku type name status price formattedPrice baseImageUrl isSaleable } }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['sku' => $product->sku, 'type' => 'simple'], $admin);
        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.adminProducts.edges.0.node');
        expect($node)->not->toBeNull();
        expect($node['sku'])->toBe($product->sku);
        expect($node['type'])->toBe('simple');
        expect($node['isSaleable'])->not->toBeNull();
    }

    protected function makeSearchProduct(string $sku): Product
    {
        $product = Product::factory()->create(['type' => 'simple', 'sku' => $sku]);

        DB::table('product_channels')->where('product_id', $product->id)->delete();

        return $product;
    }

    protected function addCustomizableOption(int $productId): void
    {
        DB::table('product_customizable_options')->insert([
            'product_id' => $productId,
            'type' => 'text',
            'is_required' => 0,
        ]);
    }

    public function test_exclude_customizable_products_and_customer_id_args(): void
    {
        $admin = $this->createAdmin();
        $prefix = 'gxc'.uniqid();
        $plain = $this->makeSearchProduct($prefix.'-plain');
        $custom = $this->makeSearchProduct($prefix.'-custom');
        $this->addCustomizableOption($custom->id);

        $channelId = core()->getDefaultChannel()->id;
        DB::table('product_channels')->insert([
            ['product_id' => $plain->id, 'channel_id' => $channelId],
            ['product_id' => $custom->id, 'channel_id' => $channelId],
        ]);
        $customer = Customer::factory()->create(['channel_id' => $channelId]);

        $query = <<<'GQL'
            query products($query: String, $customerId: Int, $exclude: Boolean) {
              adminProducts(query: $query, type: "simple", customerId: $customerId, excludeCustomizableProducts: $exclude, first: 10) {
                edges {
                  node {
                    _id
                    sku
                  }
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [
            'query' => $prefix,
            'customerId' => $customer->id,
            'exclude' => true,
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect(array_map(fn ($edge) => (int) $edge['node']['_id'], $response->json('data.adminProducts.edges')))
            ->toBe([$plain->id]);
    }
}

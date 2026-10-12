<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Product\Models\Product;

class ProductStorefrontVisibilityTest extends GraphQLTestCase
{
    private function visibleProduct(): Product
    {
        $product = $this->createBaseProduct('simple', [
            'sku' => 'STOREFRONT-VIS-'.uniqid(),
        ]);

        $this->ensureInventory($product, 10);

        return $product;
    }

    private function disable(Product $product): void
    {
        $this->upsertProductAttributeValue($product->id, 'status', 0, null, 'default');
    }

    private function unassignFromChannel(Product $product): void
    {
        DB::table('product_channels')->where('product_id', $product->id)->delete();
    }

    private function productBySku(string $sku)
    {
        return $this->graphQL(<<<GQL
            query {
              product(sku: "{$sku}") {
                sku
              }
            }
        GQL);
    }

    public function test_enabled_product_in_channel_resolves(): void
    {
        $product = $this->visibleProduct();

        $response = $this->productBySku($product->sku);

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.product.sku'))->toBe($product->sku);
    }

    public function test_disabled_product_is_not_resolved_by_sku(): void
    {
        $product = $this->visibleProduct();
        $this->disable($product);

        $response = $this->productBySku($product->sku);

        expect($response->json('errors'))->not()->toBeNull();
        expect($response->json('data.product'))->toBeNull();
    }

    public function test_disabled_product_is_not_resolved_by_id(): void
    {
        $product = $this->visibleProduct();
        $this->disable($product);

        $response = $this->graphQL(<<<GQL
            query {
              product(id: "/api/shop/products/{$product->id}") {
                sku
              }
            }
        GQL);

        expect($response->json('errors'))->not()->toBeNull();
        expect($response->json('data.product'))->toBeNull();
    }

    public function test_product_not_assigned_to_channel_is_not_resolved(): void
    {
        $product = $this->visibleProduct();
        $this->unassignFromChannel($product);

        $response = $this->productBySku($product->sku);

        expect($response->json('errors'))->not()->toBeNull();
        expect($response->json('data.product'))->toBeNull();
    }

    public function test_listing_excludes_disabled_and_unassigned_products(): void
    {
        $visible = $this->visibleProduct();
        $disabled = $this->visibleProduct();
        $unassigned = $this->visibleProduct();

        $this->disable($disabled);
        $this->unassignFromChannel($unassigned);

        $response = $this->graphQL(<<<'GQL'
            query {
              products(query: "STOREFRONT-VIS-", first: 50) {
                edges {
                  node {
                    sku
                  }
                }
              }
            }
        GQL);

        expect($response->json('errors'))->toBeNull();

        $skus = collect($response->json('data.products.edges'))->pluck('node.sku')->all();

        expect($skus)->toContain($visible->sku);
        expect($skus)->not()->toContain($disabled->sku);
        expect($skus)->not()->toContain($unassigned->sku);
    }

    public function test_cost_is_not_exposed(): void
    {
        $product = $this->visibleProduct();

        $response = $this->graphQL(<<<GQL
            query {
              product(sku: "{$product->sku}") {
                sku
                cost
              }
            }
        GQL);

        expect($response->json('errors.0.message'))->toContain('cost');
        expect($response->json('data.product.cost'))->toBeNull();
    }
}

<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Product\Models\Product;

class ProductStorefrontVisibilityTest extends GraphQLTestCase
{
    /**
     * Test enabled product in channel resolves.
     */
    public function test_enabled_product_in_channel_resolves(): void
    {
        $product = $this->visibleProduct();

        $response = $this->productBySku($product->sku);

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.product.sku'))->toBe($product->sku);
    }

    /**
     * Test disabled product is not resolved by sku.
     */
    public function test_disabled_product_is_not_resolved_by_sku(): void
    {
        $product = $this->visibleProduct();
        $this->disable($product);

        $response = $this->productBySku($product->sku);

        expect($response->json('errors'))->not()->toBeNull();
        expect($response->json('data.product'))->toBeNull();
    }

    /**
     * Test disabled product is not resolved by id.
     */
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

    /**
     * Test product not assigned to channel is not resolved.
     */
    public function test_product_not_assigned_to_channel_is_not_resolved(): void
    {
        $product = $this->visibleProduct();
        $this->unassignFromChannel($product);

        $response = $this->productBySku($product->sku);

        expect($response->json('errors'))->not()->toBeNull();
        expect($response->json('data.product'))->toBeNull();
    }

    /**
     * Test listing excludes disabled and unassigned products.
     */
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

    /**
     * Test cost is not exposed.
     */
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

    /**
     * Test product hidden from the catalog is not resolved.
     */
    public function test_product_hidden_from_the_catalog_is_not_resolved(): void
    {
        $product = $this->visibleProduct();
        $this->upsertProductAttributeValue($product->id, 'visible_individually', 0, null, 'default');

        $response = $this->productBySku($product->sku);

        expect($response->json('errors'))->not()->toBeNull();
        expect($response->json('data.product'))->toBeNull();
    }

    /**
     * Test enabled variant of a visible product resolves by sku and id.
     */
    public function test_enabled_variant_of_a_visible_product_resolves_by_sku_and_id(): void
    {
        $variant = $this->variantOf($this->visibleProduct());

        $bySku = $this->productBySku($variant->sku);

        expect($bySku->json('errors'))->toBeNull();
        expect($bySku->json('data.product.sku'))->toBe($variant->sku);

        $byId = $this->graphQL(<<<GQL
            query {
              product(id: "/api/shop/products/{$variant->id}") {
                sku
              }
            }
        GQL);

        expect($byId->json('errors'))->toBeNull();
        expect($byId->json('data.product.sku'))->toBe($variant->sku);
    }

    /**
     * Test variant of a disabled product is not resolved.
     */
    public function test_variant_of_a_disabled_product_is_not_resolved(): void
    {
        $parent = $this->visibleProduct();
        $variant = $this->variantOf($parent);
        $this->disable($parent);

        $response = $this->productBySku($variant->sku);

        expect($response->json('errors'))->not()->toBeNull();
        expect($response->json('data.product'))->toBeNull();
    }

    /**
     * Test variant is not listed.
     */
    public function test_variant_is_not_listed(): void
    {
        $variant = $this->variantOf($this->visibleProduct());

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

        $skus = collect($response->json('data.products.edges'))->pluck('node.sku')->all();

        expect($skus)->not()->toContain($variant->sku);
    }

    /**
     * Visible product.
     */
    private function visibleProduct(): Product
    {
        $product = $this->createBaseProduct('simple', [
            'sku' => 'STOREFRONT-VIS-'.uniqid(),
        ]);

        $this->ensureInventory($product, 10);

        return $product;
    }

    /**
     * Disable.
     */
    private function disable(Product $product): void
    {
        $this->upsertProductAttributeValue($product->id, 'status', 0, null, 'default');
    }

    /**
     * Unassign from channel.
     */
    private function unassignFromChannel(Product $product): void
    {
        DB::table('product_channels')->where('product_id', $product->id)->delete();
    }

    /**
     * Product by sku.
     */
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

    /**
     * Variant of.
     */
    private function variantOf(Product $parent): Product
    {
        $variant = $this->visibleProduct();

        DB::table('products')->where('id', $variant->id)->update(['parent_id' => $parent->id]);
        $this->upsertProductAttributeValue($variant->id, 'visible_individually', 0, null, 'default');

        return $variant;
    }
}

<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Product\Models\Product;

class ProductStorefrontVisibilityTest extends RestApiTestCase
{
    /**
     * Test enabled product in channel is served.
     */
    public function test_enabled_product_in_channel_is_served(): void
    {
        $product = $this->visibleProduct();

        $response = $this->publicGet('/api/shop/products/'.$product->id);

        $response->assertOk();
        expect($response->json())->not()->toHaveKey('cost');
    }

    /**
     * Test disabled product returns 404.
     */
    public function test_disabled_product_returns_404(): void
    {
        $product = $this->visibleProduct();
        $this->upsertProductAttributeValue($product->id, 'status', 0, null, 'default');

        $response = $this->publicGet('/api/shop/products/'.$product->id);

        expect($response->getStatusCode())->toBe(404);
    }

    /**
     * Test product not assigned to channel returns 404.
     */
    public function test_product_not_assigned_to_channel_returns_404(): void
    {
        $product = $this->visibleProduct();
        DB::table('product_channels')->where('product_id', $product->id)->delete();

        $response = $this->publicGet('/api/shop/products/'.$product->id);

        expect($response->getStatusCode())->toBe(404);
    }

    /**
     * Test listing excludes disabled and unassigned products.
     */
    public function test_listing_excludes_disabled_and_unassigned_products(): void
    {
        $visible = $this->visibleProduct();
        $disabled = $this->visibleProduct();
        $unassigned = $this->visibleProduct();

        $this->upsertProductAttributeValue($disabled->id, 'status', 0, null, 'default');
        DB::table('product_channels')->where('product_id', $unassigned->id)->delete();

        $response = $this->publicGet('/api/shop/products?query=STOREFRONT-REST-&per_page=50');

        $response->assertOk();

        $skus = collect($response->json())->pluck('sku')->all();

        expect($skus)->toContain($visible->sku);
        expect($skus)->not()->toContain($disabled->sku);
        expect($skus)->not()->toContain($unassigned->sku);
    }

    /**
     * Test product hidden from the catalog returns 404.
     */
    public function test_product_hidden_from_the_catalog_returns_404(): void
    {
        $product = $this->visibleProduct();
        $this->upsertProductAttributeValue($product->id, 'visible_individually', 0, null, 'default');

        expect($this->publicGet('/api/shop/products/'.$product->id)->getStatusCode())->toBe(404);
    }

    /**
     * Test enabled variant of a visible product is served by id.
     */
    public function test_enabled_variant_of_a_visible_product_is_served_by_id(): void
    {
        $variant = $this->variantOf($this->visibleProduct());

        $this->publicGet('/api/shop/products/'.$variant->id)->assertOk();
    }

    /**
     * Test variant of a disabled product returns 404.
     */
    public function test_variant_of_a_disabled_product_returns_404(): void
    {
        $parent = $this->visibleProduct();
        $variant = $this->variantOf($parent);
        $this->upsertProductAttributeValue($parent->id, 'status', 0, null, 'default');

        expect($this->publicGet('/api/shop/products/'.$variant->id)->getStatusCode())->toBe(404);
    }

    /**
     * Test disabled variant returns 404.
     */
    public function test_disabled_variant_returns_404(): void
    {
        $variant = $this->variantOf($this->visibleProduct());
        $this->upsertProductAttributeValue($variant->id, 'status', 0, null, 'default');

        expect($this->publicGet('/api/shop/products/'.$variant->id)->getStatusCode())->toBe(404);
    }

    /**
     * Test variant is not listed.
     */
    public function test_variant_is_not_listed(): void
    {
        $variant = $this->variantOf($this->visibleProduct());

        $skus = collect($this->publicGet('/api/shop/products?query=STOREFRONT-REST-&per_page=50')->json())->pluck('sku')->all();

        expect($skus)->not()->toContain($variant->sku);
    }

    /**
     * Visible product.
     */
    private function visibleProduct(): Product
    {
        $product = $this->createBaseProduct('simple', [
            'sku' => 'STOREFRONT-REST-'.uniqid(),
        ]);

        $this->ensureInventory($product, 10);

        return $product;
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

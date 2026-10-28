<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Product\Models\Product;

class ProductStorefrontVisibilityTest extends RestApiTestCase
{
    private function visibleProduct(): Product
    {
        $product = $this->createBaseProduct('simple', [
            'sku' => 'STOREFRONT-REST-'.uniqid(),
        ]);

        $this->ensureInventory($product, 10);

        return $product;
    }

    private function hideFromCatalog(Product $product): void
    {
        DB::table('product_attribute_values')
            ->where('product_id', $product->id)
            ->where('attribute_id', DB::table('attributes')->where('code', 'visible_individually')->value('id'))
            ->delete();

        $this->upsertProductAttributeValue($product->id, 'visible_individually', 0, null, 'default');
    }

    public function test_enabled_product_in_channel_is_served(): void
    {
        $product = $this->visibleProduct();

        $response = $this->publicGet('/api/shop/products/'.$product->id);

        $response->assertOk();
        expect($response->json())->not()->toHaveKey('cost');
    }

    public function test_disabled_product_returns_404(): void
    {
        $product = $this->visibleProduct();
        $this->upsertProductAttributeValue($product->id, 'status', 0, null, 'default');

        $response = $this->publicGet('/api/shop/products/'.$product->id);

        expect($response->getStatusCode())->toBe(404);
    }

    public function test_product_not_assigned_to_channel_returns_404(): void
    {
        $product = $this->visibleProduct();
        DB::table('product_channels')->where('product_id', $product->id)->delete();

        $response = $this->publicGet('/api/shop/products/'.$product->id);

        expect($response->getStatusCode())->toBe(404);
    }

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

    public function test_product_not_visible_individually_is_served_by_id(): void
    {
        $product = $this->visibleProduct();
        $this->hideFromCatalog($product);

        $response = $this->publicGet('/api/shop/products/'.$product->id);

        $response->assertOk();
        expect($response->json('sku'))->toBe($product->sku);
    }

    public function test_listing_excludes_product_not_visible_individually(): void
    {
        $visible = $this->visibleProduct();
        $hidden = $this->visibleProduct();
        $this->hideFromCatalog($hidden);

        $response = $this->publicGet('/api/shop/products?query=STOREFRONT-REST-&per_page=50');

        $response->assertOk();

        $skus = collect($response->json())->pluck('sku')->all();

        expect($skus)->toContain($visible->sku);
        expect($skus)->not()->toContain($hidden->sku);
    }
}

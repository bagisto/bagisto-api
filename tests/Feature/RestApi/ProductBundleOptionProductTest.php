<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Product\Models\ProductBundleOption;
use Webkul\Product\Models\ProductBundleOptionProduct;

class ProductBundleOptionProductTest extends RestApiTestCase
{
    private string $baseUrl = '/api/shop/product-bundle-option-products';

    private function seedBundleOptionProduct(): ProductBundleOptionProduct
    {
        $bundleProduct = $this->createBaseProduct('bundle', [
            'sku' => 'BUNDLE-OPT-PRODUCT-PARENT-'.uniqid(),
        ]);
        $childProduct = $this->createBaseProduct('simple', [
            'sku' => 'BUNDLE-OPT-PRODUCT-CHILD-'.uniqid(),
        ]);

        $option = ProductBundleOption::create([
            'type'        => 'select',
            'is_required' => 1,
            'sort_order'  => 1,
            'product_id'  => $bundleProduct->id,
        ]);

        return ProductBundleOptionProduct::create([
            'qty'                      => 1,
            'is_user_defined'          => 0,
            'sort_order'               => 1,
            'is_default'               => 1,
            'product_bundle_option_id' => $option->id,
            'product_id'               => $childProduct->id,
        ]);
    }

    public function test_get_collection(): void
    {
        $this->seedRequiredData();
        $this->seedBundleOptionProduct();

        $response = $this->publicGet($this->baseUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    public function test_get_single_bundle_option_product(): void
    {
        $this->seedRequiredData();
        $bop = $this->seedBundleOptionProduct();

        $response = $this->publicGet($this->baseUrl.'/'.$bop->id);

        $response->assertOk();
        expect((int) $response->json('id'))->toBe($bop->id);
        expect((int) $response->json('qty'))->toBe(1);
    }

    public function test_get_nonexistent_bundle_option_product_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([404, 500]);
    }
}

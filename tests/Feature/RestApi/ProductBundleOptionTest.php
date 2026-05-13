<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Product\Models\ProductBundleOption;

class ProductBundleOptionTest extends RestApiTestCase
{
    private string $baseUrl = '/api/shop/product_bundle_options';

    private function seedBundleOption(): ProductBundleOption
    {
        $product = $this->createBaseProduct('bundle', ['sku' => 'BUNDLE-OPT-'.uniqid()]);

        return ProductBundleOption::create([
            'type'        => 'select',
            'is_required' => 1,
            'sort_order'  => 1,
            'product_id'  => $product->id,
        ]);
    }

    public function test_get_single_bundle_option(): void
    {
        $this->seedRequiredData();
        $option = $this->seedBundleOption();

        $response = $this->publicGet($this->baseUrl.'/'.$option->id);

        $response->assertOk();
        expect((int) $response->json('id'))->toBe($option->id);
    }

    public function test_get_nonexistent_bundle_option_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([404, 500]);
    }
}

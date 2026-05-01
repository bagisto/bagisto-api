<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Product\Models\ProductDownloadableSample;

class ProductDownloadableSampleTest extends RestApiTestCase
{
    private string $baseUrl = '/api/shop/product-downloadable-samples';

    private function seedDownloadableSample(): ProductDownloadableSample
    {
        $product = $this->createBaseProduct('downloadable', [
            'sku' => 'DL-SAMPLE-PARENT-'.uniqid(),
        ]);

        return ProductDownloadableSample::create([
            'url'        => 'https://example.com/sample.pdf',
            'type'       => 'url',
            'sort_order' => 1,
            'product_id' => $product->id,
        ]);
    }

    public function test_get_collection(): void
    {
        $this->seedRequiredData();
        $this->seedDownloadableSample();

        $response = $this->publicGet($this->baseUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
        expect(\count($response->json()))->toBeGreaterThan(0);
    }

    public function test_get_single_downloadable_sample(): void
    {
        $this->seedRequiredData();
        $sample = $this->seedDownloadableSample();

        $response = $this->publicGet($this->baseUrl.'/'.$sample->id);

        $response->assertOk();
        expect((int) $response->json('id'))->toBe($sample->id);
    }

    public function test_get_nonexistent_sample_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([404, 500]);
    }

    public function test_nested_collection_for_product(): void
    {
        $this->seedRequiredData();
        $sample = $this->seedDownloadableSample();

        $response = $this->publicGet('/api/shop/products/'.$sample->product_id.'/downloadable-samples');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }
}

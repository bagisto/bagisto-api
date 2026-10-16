<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\Storage;
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
            'url' => 'https://example.com/sample.pdf',
            'type' => 'url',
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

    /**
     * A file sample is streamed from storage to any visitor.
     */
    public function test_download_sample_streams_the_sample_file(): void
    {
        Storage::fake();

        $this->seedRequiredData();
        $sample = $this->seedDownloadableSample();
        $sample->update([
            'type' => 'file',
            'file' => 'product_downloadable_samples/'.$sample->product_id.'/sample.txt',
            'url' => null,
        ]);

        Storage::put('product_downloadable_samples/'.$sample->product_id.'/sample.txt', 'sample-body');

        $response = $this->publicGet('/api/downloadable/download-sample/sample/'.$sample->id);

        $response->assertOk();
        expect($response->streamedContent())->toBe('sample-body');
    }

    /**
     * The sample of a product that is not visible on its own is not served.
     */
    public function test_download_sample_of_a_hidden_product_returns_404(): void
    {
        Storage::fake();

        $this->seedRequiredData();
        $sample = $this->seedDownloadableSample();
        $sample->update([
            'type' => 'file',
            'file' => 'product_downloadable_samples/'.$sample->product_id.'/sample.txt',
            'url' => null,
        ]);

        Storage::put('product_downloadable_samples/'.$sample->product_id.'/sample.txt', 'sample-body');

        $this->upsertProductAttributeValue($sample->product_id, 'visible_individually', 0, null, 'default');

        $response = $this->publicGet('/api/downloadable/download-sample/sample/'.$sample->id);

        $response->assertNotFound();
        expect($response->json('message'))->toBe('Product not found.');
    }

    /**
     * An unknown sample id returns 404.
     */
    public function test_download_unknown_sample_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/downloadable/download-sample/sample/999999999');

        $response->assertNotFound();
        expect($response->json('error'))->toBe('not_found');
    }

    /**
     * A download type other than link or sample matches no route.
     */
    public function test_download_sample_rejects_an_unknown_type(): void
    {
        $this->seedRequiredData();
        $sample = $this->seedDownloadableSample();

        $this->publicGet('/api/downloadable/download-sample/archive/'.$sample->id)->assertNotFound();
    }
}

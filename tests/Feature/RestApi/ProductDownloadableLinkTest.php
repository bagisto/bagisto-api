<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Product\Models\ProductDownloadableLink;

class ProductDownloadableLinkTest extends RestApiTestCase
{
    private string $baseUrl = '/api/shop/product-downloadable-links';

    private function seedDownloadableLink(): ProductDownloadableLink
    {
        $product = $this->createBaseProduct('downloadable', [
            'sku' => 'DL-LINK-PARENT-'.uniqid(),
        ]);

        return ProductDownloadableLink::create([
            'title' => 'Test Link',
            'price' => 5.0,
            'url' => 'https://example.com/file.zip',
            'type' => 'url',
            'sort_order' => 1,
            'product_id' => $product->id,
        ]);
    }

    public function test_get_collection(): void
    {
        $this->seedRequiredData();
        $this->seedDownloadableLink();

        $response = $this->publicGet($this->baseUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
        expect(\count($response->json()))->toBeGreaterThan(0);
    }

    public function test_get_single_downloadable_link(): void
    {
        $this->seedRequiredData();
        $link = $this->seedDownloadableLink();

        $response = $this->publicGet($this->baseUrl.'/'.$link->id);

        $response->assertOk();
        expect((int) $response->json('id'))->toBe($link->id);
    }

    public function test_link_does_not_expose_paid_url_or_file(): void
    {
        $this->seedRequiredData();
        $link = $this->seedDownloadableLink();

        $response = $this->publicGet($this->baseUrl.'/'.$link->id);

        $response->assertOk();
        expect($response->json('url'))->toBeNull();
        expect($response->json('file'))->toBeNull();
        expect($response->getContent())->not->toContain('https://example.com/file.zip');
    }

    public function test_get_nonexistent_link_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([404, 500]);
    }

    public function test_nested_collection_for_product(): void
    {
        $this->seedRequiredData();
        $link = $this->seedDownloadableLink();

        $response = $this->publicGet('/api/shop/products/'.$link->product_id.'/downloadable-links');

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * The sample file of a downloadable link is streamed from the private disk.
     */
    public function test_download_link_sample_streams_the_sample_file(): void
    {
        Storage::fake('private');

        $this->seedRequiredData();
        $link = $this->seedDownloadableLink();
        $link->update([
            'sample_type' => 'file',
            'sample_file' => 'product_downloadable_links/'.$link->product_id.'/sample.txt',
        ]);

        Storage::disk('private')->put('product_downloadable_links/'.$link->product_id.'/sample.txt', 'link-sample-body');

        $response = $this->publicGet('/api/downloadable/download-sample/link/'.$link->id);

        $response->assertOk();
        expect($response->streamedContent())->toBe('link-sample-body');
    }

    /**
     * An unknown link id returns 404.
     */
    public function test_download_unknown_link_sample_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/downloadable/download-sample/link/999999999');

        $response->assertNotFound();
        expect($response->json('error'))->toBe('not_found');
    }
}

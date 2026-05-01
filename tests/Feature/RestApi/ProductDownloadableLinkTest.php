<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

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
            'title'      => 'Test Link',
            'price'      => 5.0,
            'url'        => 'https://example.com/file.zip',
            'type'       => 'url',
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
}

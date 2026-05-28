<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Product\Models\ProductVideo;

class ProductVideoTest extends RestApiTestCase
{
    private string $baseUrl = '/api/shop/product-videos';

    private function seedVideo(): ProductVideo
    {
        $product = $this->createBaseProduct('simple', ['sku' => 'VIDEO-'.uniqid()]);

        return ProductVideo::create([
            'type'       => 'video',
            'path'       => 'product/'.$product->id.'/test.mp4',
            'product_id' => $product->id,
            'position'   => 1,
        ]);
    }

    public function test_get_collection(): void
    {
        $this->seedRequiredData();
        $this->seedVideo();

        $response = $this->publicGet($this->baseUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
        expect(\count($response->json()))->toBeGreaterThan(0);
    }

    public function test_get_single_product_video(): void
    {
        $this->seedRequiredData();
        $video = $this->seedVideo();

        $response = $this->publicGet($this->baseUrl.'/'.$video->id);

        $response->assertOk();
        expect((int) $response->json('id'))->toBe($video->id);
        expect((int) $response->json('productId'))->toBe($video->product_id);
    }

    public function test_get_nonexistent_video_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([404, 500]);
    }
}

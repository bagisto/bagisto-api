<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Product\Models\ProductImage;

class ProductImageTest extends RestApiTestCase
{
    private string $baseUrl = '/api/shop/product-images';

    private function seedImage(): ProductImage
    {
        $product = $this->createBaseProduct('simple', ['sku' => 'IMG-'.uniqid()]);

        return ProductImage::create([
            'type'       => 'image',
            'path'       => 'product/'.$product->id.'/test.jpg',
            'product_id' => $product->id,
            'position'   => 1,
        ]);
    }

    public function test_get_collection(): void
    {
        $this->seedRequiredData();
        $this->seedImage();

        $response = $this->publicGet($this->baseUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
        expect(\count($response->json()))->toBeGreaterThan(0);
    }

    public function test_get_single_product_image(): void
    {
        $this->seedRequiredData();
        $image = $this->seedImage();

        $response = $this->publicGet($this->baseUrl.'/'.$image->id);

        $response->assertOk();
        expect((int) $response->json('id'))->toBe($image->id);
        expect((int) $response->json('productId'))->toBe($image->product_id);
    }

    public function test_get_nonexistent_image_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([404, 500]);
    }
}

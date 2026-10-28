<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Product\Models\ProductImage;

class ProductImageTest extends RestApiTestCase
{
    private string $baseUrl = '/api/shop/product-images';

    private function seedImage(?string $altText = null): ProductImage
    {
        $product = $this->createBaseProduct('simple', ['sku' => 'IMG-'.uniqid()]);

        $image = ProductImage::create([
            'type' => 'image',
            'path' => 'product/'.$product->id.'/test.jpg',
            'product_id' => $product->id,
            'position' => 1,
        ]);

        if ($altText !== null) {
            $image->translateOrNew(app()->getLocale())->alt_text = $altText;

            $image->save();
        }

        return $image;
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

    public function test_image_carries_its_alt_text(): void
    {
        // BACKWARD COMPATIBILITY: remove when the minimum supported core is 2.4.10.
        $this->skipUnlessCoreSupports($this->core()->hasProductImageAltText(), 'Product image alt text');

        $this->seedRequiredData();
        $image = $this->seedImage('Blue running shoe, side view');

        $response = $this->publicGet($this->baseUrl.'/'.$image->id);

        $response->assertOk();
        expect($response->json('altText'))->toBe('Blue running shoe, side view');
        expect($response->json('fileName'))->toBe('test');
    }

    public function test_product_images_come_back_in_gallery_order(): void
    {
        $this->seedRequiredData();

        $product = $this->createBaseProduct('simple', ['sku' => 'IMG-ORDER-'.uniqid()]);

        foreach ([3, 1, 2] as $position) {
            ProductImage::create([
                'type' => 'image',
                'path' => 'product/'.$product->id.'/'.$position.'.jpg',
                'product_id' => $product->id,
                'position' => $position,
            ]);
        }

        $response = $this->publicGet('/api/shop/products/'.$product->id.'/images');

        $response->assertOk();
        expect(array_column($response->json(), 'position'))->toBe([1, 2, 3]);
    }

    public function test_get_nonexistent_image_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([404, 500]);
    }
}

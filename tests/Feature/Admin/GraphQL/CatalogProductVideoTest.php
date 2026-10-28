<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Product\Models\ProductVideo;

/**
 * GraphQL coverage for the product video reorder mutation.
 */
class CatalogProductVideoTest extends AdminApiTestCase
{
    protected function seedVideo(int $productId, int $position = 1): ProductVideo
    {
        return ProductVideo::create([
            'type' => 'videos',
            'path' => 'product/'.$productId.'/'.uniqid('vid_').'.mp4',
            'product_id' => $productId,
            'position' => $position,
        ]);
    }

    protected function reorderMutation(): string
    {
        return <<<'GQL'
            mutation reorder($input: reorderAdminCatalogProductVideoInput!) {
              reorderAdminCatalogProductVideo(input: $input) {
                adminCatalogProductVideo {
                  _id
                  success
                  message
                  videos
                }
              }
            }
        GQL;
    }

    public function test_reorder_updates_positions(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $a = $this->seedVideo($product->id, 1);
        $b = $this->seedVideo($product->id, 2);

        $response = $this->adminGraphQL($this->reorderMutation(), [
            'input' => [
                'id' => '/api/admin/catalog/products/'.$product->id.'/videos/reorder',
                'productId' => $product->id,
                'order' => [
                    ['id' => $a->id, 'position' => 6],
                    ['id' => $b->id, 'position' => 4],
                ],
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.reorderAdminCatalogProductVideo.adminCatalogProductVideo.success'))->toBeTrue();
        expect(ProductVideo::find($a->id)->position)->toBe(6);
        expect(ProductVideo::find($b->id)->position)->toBe(4);
    }

    public function test_reorder_rejects_video_of_another_product(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $other = $this->createBaseProduct('simple');
        $foreign = $this->seedVideo($other->id, 1);

        $response = $this->adminGraphQL($this->reorderMutation(), [
            'input' => [
                'id' => '/api/admin/catalog/products/'.$product->id.'/videos/reorder',
                'productId' => $product->id,
                'order' => [['id' => $foreign->id, 'position' => 9]],
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
        expect(ProductVideo::find($foreign->id)->position)->toBe(1);
    }

    public function test_reorder_requires_authentication(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');

        $response = $this->adminGraphQL($this->reorderMutation(), [
            'input' => [
                'id' => '/api/admin/catalog/products/'.$product->id.'/videos/reorder',
                'productId' => $product->id,
                'order' => [['id' => 1, 'position' => 1]],
            ],
        ]);

        expect($response->json('errors'))->not->toBeNull();
    }
}

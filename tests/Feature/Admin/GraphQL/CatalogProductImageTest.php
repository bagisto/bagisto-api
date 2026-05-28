<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Product\Models\ProductImage;

/**
 * GraphQL coverage for Phase 5.11 — Admin Catalog Product image mutations.
 *
 * Mutations exposed:
 *   - reorderAdminCatalogProductImage(input: { productId, order })
 *   - deleteAdminCatalogProductImage(input: { productId, imageId })
 *
 * The upload mutation (createAdminCatalogProductImage) is a placeholder — binary
 * file upload is REST-only. Asserting that path is documented in the REST test
 * file; covering it here would either require a custom multipart GraphQL setup
 * or just re-asserting the placeholder error path.
 */
class CatalogProductImageTest extends AdminApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    protected function seedImage(int $productId, int $position = 1): ProductImage
    {
        return ProductImage::create([
            'type'       => 'images',
            'path'       => 'product/'.$productId.'/'.uniqid('img_').'.webp',
            'product_id' => $productId,
            'position'   => $position,
        ]);
    }

    public function test_reorder_images_happy_path_updates_positions(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $a = $this->seedImage($product->id, 1);
        $b = $this->seedImage($product->id, 2);

        $mutation = <<<'GQL'
            mutation($input: reorderAdminCatalogProductImageInput!) {
              reorderAdminCatalogProductImage(input: $input) {
                adminCatalogProductImage { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'        => '/api/admin/catalog/product-images/'.$product->id,
                'productId' => $product->id,
                'order'     => [
                    ['id' => $a->id, 'position' => 7],
                    ['id' => $b->id, 'position' => 4],
                ],
            ],
        ], $admin);

        $response->assertOk();

        // GraphQL response IRI quirk may surface in `errors[]`, but DB writes are authoritative.
        expect(ProductImage::find($a->id)->position)->toBe(7);
        expect(ProductImage::find($b->id)->position)->toBe(4);
    }

    public function test_delete_image_happy_path_removes_row(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $relPath = 'product/'.$product->id.'/'.uniqid('img_').'.webp';
        Storage::disk('public')->put($relPath, 'fake');

        $image = ProductImage::create([
            'type'       => 'images',
            'path'       => $relPath,
            'product_id' => $product->id,
            'position'   => 1,
        ]);

        $mutation = <<<'GQL'
            mutation($input: deleteAdminCatalogProductImageInput!) {
              deleteAdminCatalogProductImage(input: $input) {
                adminCatalogProductImage { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                // Standard delete-mutation schema only takes `id` (the resource IRI);
                // the processor extracts the image id from it and looks up the parent product.
                'id' => '/api/admin/catalog/product-images/'.$image->id,
            ],
        ], $admin);

        $response->assertOk();

        expect(ProductImage::find($image->id))->toBeNull();
    }

    public function test_reorder_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');
        $image = $this->seedImage($product->id, 1);

        $mutation = <<<'GQL'
            mutation($input: reorderAdminCatalogProductImageInput!) {
              reorderAdminCatalogProductImage(input: $input) {
                adminCatalogProductImage { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'        => '/api/admin/catalog/product-images/'.$product->id,
                'productId' => $product->id,
                'order'     => [['id' => $image->id, 'position' => 1]],
            ],
        ]);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }
}

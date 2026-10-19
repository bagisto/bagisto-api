<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Product\Models\ProductImage;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class CatalogProductImageTest extends AdminApiTestCase
{
    /**
     * Test upload happy path returns image row.
     */
    public function test_upload_happy_path_returns_image_row(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $file = UploadedFile::fake()->image('hero.png', 200, 200);

        $response = $this->postJson(
            "/api/admin/catalog/products/{$product->id}/images",
            ['image' => $file],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(201);

        $body = $response->json();
        expect($body['productId'])->toBe($product->id);
        expect($body['path'])->toContain('product/'.$product->id.'/');
        expect($body['position'])->toBeInt();

        expect(ProductImage::where('product_id', $product->id)->count())->toBeGreaterThanOrEqual(1);
    }

    /**
     * Test upload uses explicit position.
     */
    public function test_upload_uses_explicit_position(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $file = UploadedFile::fake()->image('img.png');

        $response = $this->postJson(
            "/api/admin/catalog/products/{$product->id}/images",
            [
                'image' => $file,
                'position' => 7,
            ],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(201);
        expect($response->json('position'))->toBe(7);
    }

    /**
     * Test upload rejects invalid mime.
     */
    public function test_upload_rejects_invalid_mime(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $file = UploadedFile::fake()->create('not-an-image.pdf', 10, 'application/pdf');

        $response = $this->postJson(
            "/api/admin/catalog/products/{$product->id}/images",
            ['image' => $file],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test upload rejects missing file.
     */
    public function test_upload_rejects_missing_file(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $response = $this->postJson(
            "/api/admin/catalog/products/{$product->id}/images",
            [],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test upload to nonexistent product returns 404.
     */
    public function test_upload_to_nonexistent_product_returns_404(): void
    {
        $admin = $this->createAdmin();
        $file = UploadedFile::fake()->image('img.png');

        $response = $this->postJson(
            '/api/admin/catalog/products/9999999/images',
            ['image' => $file],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(404);
    }

    /**
     * Test upload requires admin token.
     */
    public function test_upload_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');
        $file = UploadedFile::fake()->image('img.png');

        $response = $this->postJson(
            "/api/admin/catalog/products/{$product->id}/images",
            ['image' => $file],
        );

        expect($response->getStatusCode())->toBe(401);
    }

    /**
     * Test upload rejects admin without permission.
     */
    public function test_upload_rejects_admin_without_permission(): void
    {
        $admin = $this->customRoleAdmin([]);
        $product = $this->createBaseProduct('simple');
        $file = UploadedFile::fake()->image('img.png');

        $response = $this->postJson(
            "/api/admin/catalog/products/{$product->id}/images",
            ['image' => $file],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(403);
    }

    /**
     * Test upload allows custom role with edit permission.
     */
    public function test_upload_allows_custom_role_with_edit_permission(): void
    {
        $admin = $this->customRoleAdmin(['catalog.products.edit']);
        $product = $this->createBaseProduct('simple');
        $file = UploadedFile::fake()->image('img.png');

        $response = $this->postJson(
            "/api/admin/catalog/products/{$product->id}/images",
            ['image' => $file],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(201);
    }

    /**
     * Test reorder happy path updates positions.
     */
    public function test_reorder_happy_path_updates_positions(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $a = $this->seedImage($product->id, 1);
        $b = $this->seedImage($product->id, 2);

        $response = $this->putJson(
            "/api/admin/catalog/products/{$product->id}/images/reorder",

            ['order' => [
                [
                    'id' => $a->id,
                    'position' => 5,
                ], [
                    'id' => $b->id,
                    'position' => 3,
                ],
            ]],

            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(200);
        expect(ProductImage::find($a->id)->position)->toBe(5);
        expect(ProductImage::find($b->id)->position)->toBe(3);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('images'))->toBeArray();
    }

    /**
     * Test reorder rejects id not on product.
     */
    public function test_reorder_rejects_id_not_on_product(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $other = $this->createBaseProduct('simple');

        $mine = $this->seedImage($product->id, 1);
        $foreign = $this->seedImage($other->id, 1);

        $response = $this->putJson(
            "/api/admin/catalog/products/{$product->id}/images/reorder",

            ['order' => [
                [
                    'id' => $mine->id,
                    'position' => 1,
                ], [
                    'id' => $foreign->id,
                    'position' => 2,
                ],
            ]],

            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test reorder empty payload returns 422.
     */
    public function test_reorder_empty_payload_returns_422(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $response = $this->putJson(
            "/api/admin/catalog/products/{$product->id}/images/reorder",
            ['order' => []],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(422);
    }

    /**
     * Test reorder requires admin token.
     */
    public function test_reorder_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');

        $response = $this->putJson(
            "/api/admin/catalog/products/{$product->id}/images/reorder",
            ['order' => [[
                'id' => 1,
                'position' => 1,
            ]]],
        );

        expect($response->getStatusCode())->toBe(401);
    }

    /**
     * Test reorder nonexistent product returns 404.
     */
    public function test_reorder_nonexistent_product_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->putJson(
            '/api/admin/catalog/products/9999999/images/reorder',
            ['order' => [[
                'id' => 1,
                'position' => 1,
            ]]],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(404);
    }

    /**
     * Test delete removes db row and file.
     */
    public function test_delete_removes_db_row_and_file(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $relPath = 'product/'.$product->id.'/'.uniqid('img_').'.webp';
        Storage::disk('public')->put($relPath, 'fake-bytes');

        $image = ProductImage::create([
            'type' => 'images',
            'path' => $relPath,
            'product_id' => $product->id,
            'position' => 1,
        ]);

        expect(Storage::disk('public')->exists($relPath))->toBeTrue();

        $response = $this->deleteJson(
            "/api/admin/catalog/products/{$product->id}/images/{$image->id}",
            [],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(200);
        expect(ProductImage::find($image->id))->toBeNull();
        expect(Storage::disk('public')->exists($relPath))->toBeFalse();
    }

    /**
     * Test delete image not found returns 404.
     */
    public function test_delete_image_not_found_returns_404(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $response = $this->deleteJson(
            "/api/admin/catalog/products/{$product->id}/images/9999999",
            [],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(404);
    }

    /**
     * Test delete image from other product returns 404.
     */
    public function test_delete_image_from_other_product_returns_404(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $other = $this->createBaseProduct('simple');

        $image = $this->seedImage($other->id, 1);

        $response = $this->deleteJson(
            "/api/admin/catalog/products/{$product->id}/images/{$image->id}",
            [],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(404);
        expect(ProductImage::find($image->id))->not()->toBeNull();
    }

    /**
     * Test delete requires admin token.
     */
    public function test_delete_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');
        $image = $this->seedImage($product->id, 1);

        $response = $this->deleteJson(
            "/api/admin/catalog/products/{$product->id}/images/{$image->id}",
        );

        expect($response->getStatusCode())->toBe(401);
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Seed image.
     */
    protected function seedImage(int $productId, int $position = 1): ProductImage
    {
        return ProductImage::create([
            'type' => 'images',
            'path' => 'product/'.$productId.'/'.uniqid('img_').'.webp',
            'product_id' => $productId,
            'position' => $position,
        ]);
    }

    /**
     * Custom role admin.
     */
    protected function customRoleAdmin(array $permissions = []): Admin
    {
        $role = Role::create([
            'name' => 'img-test-'.uniqid(),
            'description' => 'image-test',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }
}

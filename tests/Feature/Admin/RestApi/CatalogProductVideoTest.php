<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Product\Models\ProductVideo;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class CatalogProductVideoTest extends AdminApiTestCase
{
    /**
     * Test upload returns video row.
     */
    public function test_upload_returns_video_row(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $file = UploadedFile::fake()->create('clip.mp4', 500, 'video/mp4');

        $response = $this->postJson(
            "/api/admin/catalog/products/{$product->id}/videos",
            ['video' => $file],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(201);
        $body = $response->json();
        expect($body['productId'])->toBe($product->id);
        expect($body['path'])->toMatch('#^products/'.$product->id.'/[A-Za-z0-9]{40}\.mp4$#');
        expect($body['position'])->toBeInt();
        expect(ProductVideo::where('product_id', $product->id)->count())->toBeGreaterThanOrEqual(1);

        Storage::assertExists($body['path']);
    }

    /**
     * A file detected as a video but sent under a .php name is refused.
     */
    public function test_upload_rejects_a_video_named_php(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $this->assertUploadRefused($admin, $product->id, UploadedFile::fake()->create('x.php', 10, 'video/mp4'));
    }

    /**
     * A file detected as a video but sent under an .html name is refused.
     */
    public function test_upload_rejects_a_video_named_html(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $this->assertUploadRefused($admin, $product->id, UploadedFile::fake()->create('x.html', 10, 'video/mp4'));
    }

    /**
     * An HTML document sent under a video name is refused.
     */
    public function test_upload_rejects_html_named_as_a_video(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $this->assertUploadRefused($admin, $product->id, UploadedFile::fake()->create('clip.mp4', 10, 'text/html'));
    }

    /**
     * A custom role holding exactly the product edit permission may upload.
     */
    public function test_upload_allows_custom_role_with_edit_permission(): void
    {
        $admin = $this->customRoleAdmin(['catalog.products.edit']);
        $product = $this->createBaseProduct('simple');

        $this->postJson(
            "/api/admin/catalog/products/{$product->id}/videos",
            ['video' => UploadedFile::fake()->create('clip.webm', 10, 'video/webm')],
            $this->adminHeaders($admin),
        )->assertStatus(201);
    }

    /**
     * Test upload invalid type returns 422.
     */
    public function test_upload_invalid_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $file = UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf');

        $this->postJson(
            "/api/admin/catalog/products/{$product->id}/videos",
            ['video' => $file],
            $this->adminHeaders($admin),
        )->assertStatus(422);
    }

    /**
     * Test upload without file returns 422.
     */
    public function test_upload_without_file_returns_422(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $this->postJson(
            "/api/admin/catalog/products/{$product->id}/videos",
            [],
            $this->adminHeaders($admin),
        )->assertStatus(422);
    }

    /**
     * Test upload unknown product returns 404.
     */
    public function test_upload_unknown_product_returns_404(): void
    {
        $admin = $this->createAdmin();
        $file = UploadedFile::fake()->create('clip.mp4', 10, 'video/mp4');

        $this->postJson(
            '/api/admin/catalog/products/999999/videos',
            ['video' => $file],
            $this->adminHeaders($admin),
        )->assertStatus(404);
    }

    /**
     * Test upload requires authentication.
     */
    public function test_upload_requires_authentication(): void
    {
        $product = $this->createBaseProduct('simple');
        $file = UploadedFile::fake()->create('clip.mp4', 10, 'video/mp4');

        $this->postJson("/api/admin/catalog/products/{$product->id}/videos", ['video' => $file])
            ->assertStatus(401);
    }

    /**
     * Test upload no permission returns 403.
     */
    public function test_upload_no_permission_returns_403(): void
    {
        $admin = $this->customRoleAdmin([]);
        $product = $this->createBaseProduct('simple');
        $file = UploadedFile::fake()->create('clip.mp4', 10, 'video/mp4');

        $this->postJson(
            "/api/admin/catalog/products/{$product->id}/videos",
            ['video' => $file],
            $this->adminHeaders($admin),
        )->assertStatus(403);
    }

    /**
     * Test delete removes row.
     */
    public function test_delete_removes_row(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $video = $this->seedVideo($product->id);

        $response = $this->deleteJson(
            "/api/admin/catalog/products/{$product->id}/videos/{$video->id}",
            [],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(200);
        expect(ProductVideo::find($video->id))->toBeNull();
    }

    /**
     * Test delete cross product returns 404.
     */
    public function test_delete_cross_product_returns_404(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $video = $this->seedVideo($product->id);

        $this->deleteJson(
            "/api/admin/catalog/products/999999/videos/{$video->id}",
            [],
            $this->adminHeaders($admin),
        )->assertStatus(404);
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        Storage::fake(config('filesystems.default'));
    }

    /**
     * Post the file and assert it is refused with 422 and no video row or file is stored.
     */
    protected function assertUploadRefused(Admin $admin, int $productId, UploadedFile $file): void
    {
        $response = $this->postJson(
            "/api/admin/catalog/products/{$productId}/videos",
            ['video' => $file],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(422);
        expect($response->json('detail') ?? $response->json('message'))->toBe(trans('bagistoapi::app.admin.product.video.video-invalid-type'));
        expect(ProductVideo::where('product_id', $productId)->exists())->toBeFalse();
        expect(Storage::allFiles('products/'.$productId))->toBe([]);
    }

    /**
     * Custom role admin.
     */
    protected function customRoleAdmin(array $permissions = []): Admin
    {
        $role = Role::create([
            'name' => 'vid-test-'.uniqid(),
            'description' => 'video-test',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    /**
     * Seed video.
     */
    protected function seedVideo(int $productId, int $position = 1): ProductVideo
    {
        return ProductVideo::create([
            'type' => 'videos',
            'path' => 'product/'.$productId.'/'.uniqid('vid_').'.mp4',
            'product_id' => $productId,
            'position' => $position,
        ]);
    }
}

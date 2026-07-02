<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Product\Models\ProductVideo;

class CatalogProductVideoTest extends AdminApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    protected function customRoleAdmin(array $permissions = []): \Webkul\User\Models\Admin
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'vid-test-'.uniqid(),
            'description'     => 'video-test',
            'permission_type' => 'custom',
            'permissions'     => $permissions,
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    protected function seedVideo(int $productId, int $position = 1): ProductVideo
    {
        return ProductVideo::create([
            'type'       => 'videos',
            'path'       => 'product/'.$productId.'/'.uniqid('vid_').'.mp4',
            'product_id' => $productId,
            'position'   => $position,
        ]);
    }

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
        expect($body['path'])->toContain('product/'.$product->id.'/');
        expect($body['position'])->toBeInt();
        expect(ProductVideo::where('product_id', $product->id)->count())->toBeGreaterThanOrEqual(1);
    }

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

    public function test_upload_requires_authentication(): void
    {
        $product = $this->createBaseProduct('simple');
        $file = UploadedFile::fake()->create('clip.mp4', 10, 'video/mp4');

        $this->postJson("/api/admin/catalog/products/{$product->id}/videos", ['video' => $file])
            ->assertStatus(401);
    }

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
}

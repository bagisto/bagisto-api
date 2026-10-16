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
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

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

    protected function seedVideo(int $productId, int $position = 1): ProductVideo
    {
        return ProductVideo::create([
            'type' => 'videos',
            'path' => 'product/'.$productId.'/'.uniqid('vid_').'.mp4',
            'product_id' => $productId,
            'position' => $position,
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

    public function test_reorder_updates_positions(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $a = $this->seedVideo($product->id, 1);
        $b = $this->seedVideo($product->id, 2);

        $response = $this->putJson(
            "/api/admin/catalog/products/{$product->id}/videos/reorder",
            ['order' => [
                ['id' => $a->id, 'position' => 2],
                ['id' => $b->id, 'position' => 1],
            ]],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(200);
        expect(ProductVideo::find($a->id)->position)->toBe(2);
        expect(ProductVideo::find($b->id)->position)->toBe(1);
        expect($response->json('success'))->toBeTrue();
        expect($response->json('videos.0.id'))->toBe($b->id);
    }

    public function test_reorder_rejects_video_of_another_product(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $other = $this->createBaseProduct('simple');
        $foreign = $this->seedVideo($other->id, 1);

        $this->putJson(
            "/api/admin/catalog/products/{$product->id}/videos/reorder",
            ['order' => [['id' => $foreign->id, 'position' => 1]]],
            $this->adminHeaders($admin),
        )->assertStatus(422);

        expect(ProductVideo::find($foreign->id)->position)->toBe(1);
    }

    public function test_reorder_empty_payload_returns_422(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $this->putJson(
            "/api/admin/catalog/products/{$product->id}/videos/reorder",
            ['order' => []],
            $this->adminHeaders($admin),
        )->assertStatus(422);
    }

    public function test_reorder_unknown_product_returns_404(): void
    {
        $admin = $this->createAdmin();

        $this->putJson(
            '/api/admin/catalog/products/9999999/videos/reorder',
            ['order' => [['id' => 1, 'position' => 1]]],
            $this->adminHeaders($admin),
        )->assertStatus(404);
    }

    public function test_reorder_requires_authentication(): void
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');

        $this->putJson(
            "/api/admin/catalog/products/{$product->id}/videos/reorder",
            ['order' => [['id' => 1, 'position' => 1]]],
        )->assertStatus(401);
    }

    public function test_reorder_without_permission_returns_403(): void
    {
        $admin = $this->customRoleAdmin(['catalog.products']);
        $product = $this->createBaseProduct('simple');
        $video = $this->seedVideo($product->id, 1);

        $this->putJson(
            "/api/admin/catalog/products/{$product->id}/videos/reorder",
            ['order' => [['id' => $video->id, 'position' => 3]]],
            $this->adminHeaders($admin),
        )->assertStatus(403);
    }
}

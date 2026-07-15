<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class CatalogProductDownloadableFileTest extends AdminApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private');
    }

    protected function customRoleAdmin(array $permissions = []): Admin
    {
        $role = Role::create([
            'name' => 'dl-test-'.uniqid(),
            'description' => 'downloadable-test',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    // ---- CAT1: upload ----

    public function test_upload_link_file_returns_stored_path(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $file = UploadedFile::fake()->create('manual.zip', 100);

        $response = $this->postJson(
            "/api/admin/catalog/products/{$product->id}/downloadable-links/upload",
            ['file' => $file],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(201);
        $body = $response->json();
        expect($body['type'])->toBe('link');
        expect($body['path'])->toContain('product_downloadable_links/'.$product->id);
        expect($body['name'])->toBe('manual.zip');
        Storage::disk('private')->assertExists($body['path']);
    }

    public function test_upload_sample_file_returns_stored_path(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $file = UploadedFile::fake()->create('preview.zip', 50);

        $response = $this->postJson(
            "/api/admin/catalog/products/{$product->id}/downloadable-samples/upload",
            ['file' => $file],
            $this->adminHeaders($admin),
        );

        expect($response->getStatusCode())->toBe(201);
        $body = $response->json();
        expect($body['type'])->toBe('sample');
        expect($body['path'])->toContain('product_downloadable_samples/'.$product->id);
        Storage::disk('private')->assertExists($body['path']);
    }

    public function test_upload_without_file_returns_422(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $this->postJson(
            "/api/admin/catalog/products/{$product->id}/downloadable-links/upload",
            [],
            $this->adminHeaders($admin),
        )->assertStatus(422);
    }

    public function test_upload_unknown_product_returns_404(): void
    {
        $admin = $this->createAdmin();
        $file = UploadedFile::fake()->create('x.zip', 10);

        $this->postJson(
            '/api/admin/catalog/products/999999/downloadable-links/upload',
            ['file' => $file],
            $this->adminHeaders($admin),
        )->assertStatus(404);
    }

    public function test_upload_requires_authentication(): void
    {
        $product = $this->createBaseProduct('simple');
        $file = UploadedFile::fake()->create('x.zip', 10);

        $this->postJson("/api/admin/catalog/products/{$product->id}/downloadable-links/upload", ['file' => $file])
            ->assertStatus(401);
    }

    public function test_upload_no_permission_returns_403(): void
    {
        $admin = $this->customRoleAdmin([]);
        $product = $this->createBaseProduct('simple');
        $file = UploadedFile::fake()->create('x.zip', 10);

        $this->postJson(
            "/api/admin/catalog/products/{$product->id}/downloadable-links/upload",
            ['file' => $file],
            $this->adminHeaders($admin),
        )->assertStatus(403);
    }

    // ---- CAT3: download ----

    protected function seedStoredAttributeFile(int $productId, int $attributeId): string
    {
        $path = UploadedFile::fake()->create('stored.zip', 20)
            ->store('product_downloadable_links/'.$productId, 'private');

        DB::table('product_attribute_values')->insert([
            'product_id' => $productId,
            'attribute_id' => $attributeId,
            'text_value' => $path,
            'unique_id' => $productId.'|'.$attributeId,
            'channel' => null,
            'locale' => null,
        ]);

        return $path;
    }

    public function test_download_streams_stored_file(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $attributeId = 26;
        $this->seedStoredAttributeFile($product->id, $attributeId);

        $response = $this->get(
            "/api/admin/catalog/products/{$product->id}/downloadable/{$attributeId}/download",
            array_merge($this->adminHeaders($admin), ['Accept' => 'application/octet-stream']),
        );

        expect($response->getStatusCode())->toBe(200);
    }

    public function test_download_unknown_returns_404(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $this->get(
            "/api/admin/catalog/products/{$product->id}/downloadable/999999/download",
            array_merge($this->adminHeaders($admin), ['Accept' => 'application/octet-stream']),
        )->assertStatus(404);
    }

    public function test_download_requires_authentication(): void
    {
        $product = $this->createBaseProduct('simple');

        $this->get(
            "/api/admin/catalog/products/{$product->id}/downloadable/26/download",
            ['Accept' => 'application/octet-stream'],
        )->assertStatus(401);
    }
}

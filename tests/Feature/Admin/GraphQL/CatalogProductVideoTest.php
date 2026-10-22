<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Product\Models\ProductVideo;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class CatalogProductVideoTest extends AdminApiTestCase
{
    /**
     * The create mutation refuses the upload and points at the REST endpoint, storing nothing.
     */
    public function test_create_mutation_refuses_upload_over_graphql(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $response = $this->runCreate($product->id, $admin);

        $response->assertOk();

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.product.video.graphql-upload-unsupported'));
        expect(ProductVideo::where('product_id', $product->id)->exists())->toBeFalse();
    }

    /**
     * The create mutation refuses a request without an admin token.
     */
    public function test_create_mutation_requires_authentication(): void
    {
        $product = $this->createBaseProduct('simple');

        $response = $this->runCreate($product->id);

        expect($response->getStatusCode() === 401 || $response->json('errors') !== null)->toBeTrue();
        expect(ProductVideo::where('product_id', $product->id)->exists())->toBeFalse();
    }

    /**
     * The create mutation refuses a role without the product edit permission.
     */
    public function test_create_mutation_requires_the_edit_permission(): void
    {
        [$admin, $token] = $this->customRoleAdmin(['catalog', 'catalog.products']);
        $product = $this->createBaseProduct('simple');

        $response = $this->runCreate($product->id, $admin, $token);

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.product.video.no-permission'));
    }

    /**
     * The delete mutation removes the video row and its file.
     */
    public function test_delete_mutation_removes_the_row_and_file(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');
        $video = $this->seedVideo($product->id);

        $response = $this->runDelete($video->id, $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();
        expect(ProductVideo::find($video->id))->toBeNull();

        Storage::disk('public')->assertMissing($video->path);
    }

    /**
     * The delete mutation reports an unknown video.
     */
    public function test_delete_mutation_rejects_an_unknown_video(): void
    {
        $admin = $this->createAdmin();

        $response = $this->runDelete(999999999, $admin);

        expect($response->json('errors'))->not->toBeNull();
        expect($response->json('data.deleteAdminCatalogProductVideo.adminCatalogProductVideo'))->toBeNull();
    }

    /**
     * The delete mutation refuses a request without an admin token and keeps the video.
     */
    public function test_delete_mutation_requires_authentication(): void
    {
        $product = $this->createBaseProduct('simple');
        $video = $this->seedVideo($product->id);

        $response = $this->runDelete($video->id);

        expect($response->getStatusCode() === 401 || $response->json('errors') !== null)->toBeTrue();
        expect(ProductVideo::find($video->id))->not->toBeNull();
    }

    /**
     * The delete mutation refuses a role without the product edit permission and keeps the video.
     */
    public function test_delete_mutation_requires_the_edit_permission(): void
    {
        [$admin, $token] = $this->customRoleAdmin(['catalog', 'catalog.products']);
        $product = $this->createBaseProduct('simple');
        $video = $this->seedVideo($product->id);

        $response = $this->runDelete($video->id, $admin, $token);

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.product.video.no-permission'));
        expect(ProductVideo::find($video->id))->not->toBeNull();
    }

    /**
     * Keep video files on a temporary public disk.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    /**
     * Create an admin whose role holds only the given permissions, with a role-bound token.
     */
    protected function customRoleAdmin(array $permissions = []): array
    {
        $this->seedRequiredData();

        $role = Role::create([
            'name' => 'vid-gql-'.uniqid(),
            'description' => 'video-test',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);

        return [$admin, $this->adminAccessToken($admin)];
    }

    /**
     * Store a video file on the fake disk and attach it to the product.
     */
    protected function seedVideo(int $productId, int $position = 1): ProductVideo
    {
        $path = 'product/'.$productId.'/'.uniqid('vid_').'.mp4';

        Storage::disk('public')->put($path, 'fake');

        return ProductVideo::create([
            'type' => 'videos',
            'path' => $path,
            'product_id' => $productId,
            'position' => $position,
        ]);
    }

    /**
     * Run the create (upload placeholder) mutation.
     */
    protected function runCreate(int $productId, ?Admin $admin = null, ?string $token = null): TestResponse
    {
        $mutation = <<<'GQL'
            mutation($input: createAdminCatalogProductVideoInput!) {
              createAdminCatalogProductVideo(input: $input) {
                adminCatalogProductVideo { _id }
              }
            }
        GQL;

        return $this->adminGraphQL($mutation, ['input' => ['productId' => $productId]], $admin, $token);
    }

    /**
     * Run the delete mutation for the given video id.
     */
    protected function runDelete(int $videoId, ?Admin $admin = null, ?string $token = null): TestResponse
    {
        $mutation = <<<'GQL'
            mutation($input: deleteAdminCatalogProductVideoInput!) {
              deleteAdminCatalogProductVideo(input: $input) {
                adminCatalogProductVideo { _id }
              }
            }
        GQL;

        return $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/catalog/product-videos/'.$videoId],
        ], $admin, $token);
    }
}

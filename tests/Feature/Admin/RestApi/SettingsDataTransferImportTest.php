<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for Admin Settings → Data Transfer Imports (Block B Wave 3).
 *
 * Endpoints:
 *   GET    /api/admin/settings/data-transfer/imports
 *   GET    /api/admin/settings/data-transfer/imports/{id}
 *   POST   /api/admin/settings/data-transfer/imports/{id}/cancel
 *   DELETE /api/admin/settings/data-transfer/imports/{id}
 */
class SettingsDataTransferImportTest extends AdminApiTestCase
{
    protected function insertImport(array $overrides = []): int
    {
        return (int) \DB::table('imports')->insertGetId(array_merge([
            'state'                => 'pending',
            'process_in_queue'     => 1,
            'type'                 => 'product',
            'action'               => 'append',
            'validation_strategy'  => 'stop-on-errors',
            'allowed_errors'       => 0,
            'processed_rows_count' => 0,
            'invalid_rows_count'   => 0,
            'errors_count'         => 0,
            'field_separator'      => ',',
            'file_path'            => 'imports/sample-'.uniqid().'.csv',
            'created_at'           => now(),
            'updated_at'           => now(),
        ], $overrides));
    }

    protected function adminDelete(\Webkul\User\Models\Admin $admin, string $url, ?string $token = null): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    protected function createAdminWithoutPermissions(): \Webkul\User\Models\Admin
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'NoDataTransfer '.uniqid(),
            'description'     => 'no perms',
            'permission_type' => 'custom',
            'permissions'     => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/settings/data-transfer/imports');
        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertImport();

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertImport(['type' => 'product', 'action' => 'append', 'state' => 'pending']);

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys(['id', 'code', 'action', 'state', 'createdAt']);
    }

    public function test_filter_by_code(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertImport(['type' => 'product']);
        $b = $this->insertImport(['type' => 'customer']);

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?code=customer');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($b);
        expect($ids)->not->toContain($a);
    }

    public function test_filter_by_type_alias(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertImport(['type' => 'product']);
        $b = $this->insertImport(['type' => 'category']);

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?type=category');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($b);
        expect($ids)->not->toContain($a);
    }

    public function test_filter_by_action(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertImport(['action' => 'append']);
        $b = $this->insertImport(['action' => 'delete']);

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?action=delete');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($b);
        expect($ids)->not->toContain($a);
    }

    public function test_filter_by_state(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertImport(['state' => 'pending']);
        $b = $this->insertImport(['state' => 'completed']);

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?state=completed');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($b);
        expect($ids)->not->toContain($a);
    }

    public function test_filter_by_created_at_range(): void
    {
        $admin = $this->createAdmin();
        $old = $this->insertImport(['created_at' => now()->subMonth(), 'updated_at' => now()->subMonth()]);
        $new = $this->insertImport();

        $from = now()->subDays(2)->toDateString();
        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?created_at_from='.$from);
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($new);
        expect($ids)->not->toContain($old);
    }

    public function test_sort_id_desc_default(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertImport();
        $b = $this->insertImport();

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports?per_page=2');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids[0])->toBe($b);
    }

    public function test_detail_requires_admin_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicGet('/api/admin/settings/data-transfer/imports/'.$id);
        $response->assertStatus(401);
    }

    public function test_detail_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['type' => 'product', 'state' => 'pending']);

        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('code'))->toBe('product');
        expect($response->json('state'))->toBe('pending');
        expect($response->json('filePath'))->toBeString();
    }

    public function test_detail_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/data-transfer/imports/999999');
        $response->assertStatus(404);
    }

    public function test_cancel_pending_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['state' => 'pending']);

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/cancel');

        $response->assertOk();
        expect($response->json('success'))->toBeTrue();
        expect($response->json('state'))->toBe('cancelled');

        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('cancelled');
    }

    public function test_cancel_processing_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['state' => 'processing']);

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/cancel');

        $response->assertOk();
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('cancelled');
    }

    public function test_cancel_completed_is_refused(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport(['state' => 'completed']);

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/cancel');

        $response->assertStatus(422);
        expect(\DB::table('imports')->where('id', $id)->value('state'))->toBe('completed');
    }

    public function test_cancel_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/999999/cancel');
        $response->assertStatus(404);
    }

    public function test_cancel_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->publicPost('/api/admin/settings/data-transfer/imports/'.$id.'/cancel');
        $response->assertStatus(401);
    }

    public function test_cancel_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertImport();

        $response = $this->adminPost($admin, '/api/admin/settings/data-transfer/imports/'.$id.'/cancel');
        $response->assertStatus(403);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertImport();

        $response = $this->adminDelete($admin, '/api/admin/settings/data-transfer/imports/'.$id);

        $response->assertOk();
        expect(\DB::table('imports')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_delete_not_found(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/settings/data-transfer/imports/999999');
        $response->assertStatus(404);
    }

    public function test_delete_requires_token(): void
    {
        $id = $this->insertImport();
        $response = $this->deleteJson('/api/admin/settings/data-transfer/imports/'.$id);
        $response->assertStatus(401);
    }

    public function test_delete_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertImport();

        $response = $this->adminDelete($admin, '/api/admin/settings/data-transfer/imports/'.$id);
        $response->assertStatus(403);
    }
}

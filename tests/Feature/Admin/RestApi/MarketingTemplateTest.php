<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for Admin Marketing → Email Templates CRUD (Block F2a).
 */
class MarketingTemplateTest extends AdminApiTestCase
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function insertTemplate(array $overrides = []): int
    {
        return \DB::table('marketing_templates')->insertGetId(array_merge([
            'name'       => 'Template '.uniqid(),
            'status'     => 'active',
            'content'    => '<p>Body</p>',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function adminPut(\Webkul\User\Models\Admin $admin, string $url, array $data = [], ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete(\Webkul\User\Models\Admin $admin, string $url, ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    protected function createAdminWithoutPermissions(): \Webkul\User\Models\Admin
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'Limited '.uniqid(),
            'description'     => 'no template perms',
            'permission_type' => 'custom',
            'permissions'     => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    protected function basePayload(array $overrides = []): array
    {
        return array_merge([
            'name'    => 'API Template '.uniqid(),
            'status'  => 'active',
            'content' => '<p>Welcome</p>',
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Listing
    // -------------------------------------------------------------------------

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/marketing/templates');
        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertTemplate();

        $response = $this->adminGet($admin, '/api/admin/marketing/templates');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertTemplate();

        $response = $this->adminGet($admin, '/api/admin/marketing/templates?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys(['id', 'name', 'status', 'content']);
    }

    public function test_filter_by_name(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTemplate(['name' => 'UniqueTemplate-X']);
        $this->insertTemplate(['name' => 'Other Template']);

        $response = $this->adminGet($admin, '/api/admin/marketing/templates?name=UniqueTemplate');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        $on = $this->insertTemplate(['status' => 'active', 'name' => 'active-tpl']);
        $off = $this->insertTemplate(['status' => 'inactive', 'name' => 'inactive-tpl']);

        $response = $this->adminGet($admin, '/api/admin/marketing/templates?status=inactive');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($off);
        expect($ids)->not->toContain($on);
    }

    public function test_sort_by_name_asc(): void
    {
        $admin = $this->createAdmin();
        $this->insertTemplate(['name' => 'zzz-srt-tpl']);
        $this->insertTemplate(['name' => 'aaa-srt-tpl']);

        $response = $this->adminGet($admin, '/api/admin/marketing/templates?sort=name&order=asc');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();
        $idxA = array_search('aaa-srt-tpl', $names, true);
        $idxZ = array_search('zzz-srt-tpl', $names, true);

        if ($idxA !== false && $idxZ !== false) {
            expect($idxA)->toBeLessThan($idxZ);
        }
    }

    public function test_per_page_cap(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/marketing/templates?per_page=999');

        $response->assertOk();
        expect((int) $response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function test_detail_returns_payload(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTemplate(['name' => 'Detail Tpl', 'content' => '<h1>Hi</h1>']);

        $response = $this->adminGet($admin, '/api/admin/marketing/templates/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('name'))->toBe('Detail Tpl');
        expect($response->json('content'))->toBe('<h1>Hi</h1>');
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/marketing/templates/999999');
        $response->assertStatus(404);
    }

    public function test_detail_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertTemplate();
        $response = $this->publicGet('/api/admin/marketing/templates/'.$id);
        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_happy_path(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/marketing/templates', $this->basePayload([
            'name'    => 'Created Tpl',
            'status'  => 'draft',
            'content' => '<p>Body</p>',
        ]));

        $response->assertStatus(201);
        $id = $response->json('id');
        $this->assertDatabaseHas('marketing_templates', ['id' => $id, 'name' => 'Created Tpl', 'status' => 'draft']);
    }

    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['name']);
        $response = $this->adminPost($admin, '/api/admin/marketing/templates', $payload);
        $response->assertStatus(422);
    }

    public function test_create_missing_status_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['status']);
        $response = $this->adminPost($admin, '/api/admin/marketing/templates', $payload);
        $response->assertStatus(422);
    }

    public function test_create_missing_content_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['content']);
        $response = $this->adminPost($admin, '/api/admin/marketing/templates', $payload);
        $response->assertStatus(422);
    }

    public function test_create_invalid_status_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/marketing/templates', $this->basePayload([
            'status' => 'bogus',
        ]));
        $response->assertStatus(422);
    }

    public function test_create_all_three_status_values_accepted(): void
    {
        $admin = $this->createAdmin();
        foreach (['active', 'inactive', 'draft'] as $status) {
            $response = $this->adminPost($admin, '/api/admin/marketing/templates', $this->basePayload([
                'name'   => 'enum-'.$status.'-'.uniqid(),
                'status' => $status,
            ]));
            $response->assertStatus(201);
        }
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->publicPost('/api/admin/marketing/templates', ['name' => 'X']);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_create_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $response = $this->adminPost($admin, '/api/admin/marketing/templates', $this->basePayload());
        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTemplate(['name' => 'Before']);

        $response = $this->adminPut($admin, '/api/admin/marketing/templates/'.$id, $this->basePayload([
            'name'    => 'After',
            'status'  => 'inactive',
            'content' => '<p>New</p>',
        ]));

        $response->assertOk();
        expect($response->json('name'))->toBe('After');
        $this->assertDatabaseHas('marketing_templates', ['id' => $id, 'name' => 'After', 'status' => 'inactive']);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/marketing/templates/999999', $this->basePayload());
        $response->assertStatus(404);
    }

    public function test_update_invalid_status_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTemplate();
        $response = $this->adminPut($admin, '/api/admin/marketing/templates/'.$id, $this->basePayload([
            'status' => 'invalid',
        ]));
        $response->assertStatus(422);
    }

    public function test_update_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertTemplate();
        $response = $this->putJson('/api/admin/marketing/templates/'.$id, ['name' => 'X']);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_update_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertTemplate();
        $response = $this->adminPut($admin, '/api/admin/marketing/templates/'.$id, $this->basePayload());
        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTemplate();

        $response = $this->adminDelete($admin, '/api/admin/marketing/templates/'.$id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('marketing_templates', ['id' => $id]);
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/marketing/templates/999999');
        $response->assertStatus(404);
    }

    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertTemplate();
        $response = $this->deleteJson('/api/admin/marketing/templates/'.$id);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_delete_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertTemplate();
        $response = $this->adminDelete($admin, '/api/admin/marketing/templates/'.$id);
        $response->assertStatus(403);
    }
}

<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

/**
 * REST coverage for Admin Marketing → Events CRUD (Block F2b).
 */
class MarketingEventTest extends AdminApiTestCase
{
    protected function insertEvent(array $overrides = []): int
    {
        return \DB::table('marketing_events')->insertGetId(array_merge([
            'name' => 'Event '.uniqid(),
            'description' => 'desc',
            'date' => '2026-12-20',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function adminPut(Admin $admin, string $url, array $data = [], ?string $token = null): TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete(Admin $admin, string $url, ?string $token = null): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    protected function createAdminWithoutPermissions(): Admin
    {
        $role = Role::create([
            'name' => 'Limited '.uniqid(),
            'description' => 'no event perms',
            'permission_type' => 'custom',
            'permissions' => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    protected function basePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'API Event '.uniqid(),
            'description' => 'via api',
            'date' => '2027-01-15',
        ], $overrides);
    }

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/marketing/events');
        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertEvent();

        $response = $this->adminGet($admin, '/api/admin/marketing/events');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertEvent();

        $response = $this->adminGet($admin, '/api/admin/marketing/events?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys(['id', 'name', 'description', 'date']);
    }

    public function test_filter_by_name(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertEvent(['name' => 'UniqueEvtName-X']);
        $this->insertEvent(['name' => 'Other Event']);

        $response = $this->adminGet($admin, '/api/admin/marketing/events?name=UniqueEvtName');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_filter_by_date_range(): void
    {
        $admin = $this->createAdmin();
        $early = $this->insertEvent(['date' => '2026-01-15', 'name' => 'early-evt']);
        $mid = $this->insertEvent(['date' => '2026-06-15', 'name' => 'mid-evt']);
        $late = $this->insertEvent(['date' => '2027-01-15', 'name' => 'late-evt']);

        $response = $this->adminGet($admin, '/api/admin/marketing/events?date_from=2026-03-01&date_to=2026-12-31');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($mid);
        expect($ids)->not->toContain($early);
        expect($ids)->not->toContain($late);
    }

    public function test_sort_by_name_asc(): void
    {
        $admin = $this->createAdmin();
        $this->insertEvent(['name' => 'zzz-srt-evt']);
        $this->insertEvent(['name' => 'aaa-srt-evt']);

        $response = $this->adminGet($admin, '/api/admin/marketing/events?sort=name&order=asc');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();
        $idxA = array_search('aaa-srt-evt', $names, true);
        $idxZ = array_search('zzz-srt-evt', $names, true);

        if ($idxA !== false && $idxZ !== false) {
            expect($idxA)->toBeLessThan($idxZ);
        }
    }

    public function test_per_page_cap(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/marketing/events?per_page=999');

        $response->assertOk();
        expect((int) $response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_detail_returns_payload(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertEvent(['name' => 'Detail Event']);

        $response = $this->adminGet($admin, '/api/admin/marketing/events/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('name'))->toBe('Detail Event');
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/marketing/events/999999');
        $response->assertStatus(404);
    }

    public function test_detail_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertEvent();
        $response = $this->publicGet('/api/admin/marketing/events/'.$id);
        $response->assertStatus(401);
    }

    public function test_create_happy_path(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/marketing/events', $this->basePayload([
            'name' => 'Created Event',
            'date' => '2027-06-01',
        ]));

        $response->assertStatus(201);
        $id = $response->json('id');
        $this->assertDatabaseHas('marketing_events', ['id' => $id, 'name' => 'Created Event']);
        expect($response->json('date'))->toContain('2027-06-01');
    }

    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['name']);
        $response = $this->adminPost($admin, '/api/admin/marketing/events', $payload);
        $response->assertStatus(422);
    }

    public function test_create_missing_description_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['description']);
        $response = $this->adminPost($admin, '/api/admin/marketing/events', $payload);
        $response->assertStatus(422);
    }

    public function test_create_missing_date_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['date']);
        $response = $this->adminPost($admin, '/api/admin/marketing/events', $payload);
        $response->assertStatus(422);
    }

    public function test_create_invalid_date_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/marketing/events', $this->basePayload([
            'date' => 'not-a-date',
        ]));
        $response->assertStatus(422);
    }

    public function test_create_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicPost('/api/admin/marketing/events', $this->basePayload());
        $response->assertStatus(401);
    }

    public function test_create_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $response = $this->adminPost($admin, '/api/admin/marketing/events', $this->basePayload());
        $response->assertStatus(403);
    }

    public function test_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertEvent(['name' => 'old-name']);

        $response = $this->adminPut($admin, '/api/admin/marketing/events/'.$id, [
            'name' => 'updated-name',
            'description' => 'updated desc',
            'date' => '2028-02-29',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('marketing_events', ['id' => $id, 'name' => 'updated-name']);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/marketing/events/999999', [
            'name' => 'x',
            'description' => 'y',
            'date' => '2027-01-01',
        ]);
        $response->assertStatus(404);
    }

    public function test_update_invalid_date_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertEvent();
        $response = $this->adminPut($admin, '/api/admin/marketing/events/'.$id, [
            'name' => 'x',
            'description' => 'y',
            'date' => 'garbage',
        ]);
        $response->assertStatus(422);
    }

    public function test_update_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertEvent();
        $response = $this->adminPut($admin, '/api/admin/marketing/events/'.$id, [
            'name' => 'x',
            'description' => 'y',
            'date' => '2027-01-01',
        ]);
        $response->assertStatus(403);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertEvent();

        $response = $this->adminDelete($admin, '/api/admin/marketing/events/'.$id);

        $response->assertOk();
        $this->assertDatabaseMissing('marketing_events', ['id' => $id]);
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/marketing/events/999999');
        $response->assertStatus(404);
    }

    public function test_delete_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertEvent();
        $response = $this->deleteJson('/api/admin/marketing/events/'.$id);
        $response->assertStatus(401);
    }

    public function test_delete_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertEvent();
        $response = $this->adminDelete($admin, '/api/admin/marketing/events/'.$id);
        $response->assertStatus(403);
    }
}

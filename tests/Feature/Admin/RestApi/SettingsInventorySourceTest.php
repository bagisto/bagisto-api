<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\User\Models\Admin;

/**
 * REST coverage for the admin Settings → Inventory Sources CRUD endpoints
 * (Block B Wave 1).
 *
 * Endpoints:
 *   GET    /api/admin/settings/inventory-sources
 *   GET    /api/admin/settings/inventory-sources/{id}
 *   POST   /api/admin/settings/inventory-sources
 *   PUT    /api/admin/settings/inventory-sources/{id}
 *   DELETE /api/admin/settings/inventory-sources/{id}
 *   POST   /api/admin/settings/inventory-sources/mass-delete
 */
class SettingsInventorySourceTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    protected function insertInventorySource(array $overrides = []): int
    {
        return \DB::table('inventory_sources')->insertGetId(array_merge([
            'code' => 'src-'.substr((string) microtime(true), -4).rand(10, 99),
            'name' => 'Source '.rand(100, 999),
            'description' => null,
            'contact_name' => 'Jane Doe',
            'contact_email' => 'jane'.rand(100, 999).'@example.com',
            'contact_number' => '1234567890',
            'contact_fax' => null,
            'country' => 'US',
            'state' => 'CA',
            'city' => 'Los Angeles',
            'street' => '123 Main St',
            'postcode' => '90001',
            'priority' => 0,
            'latitude' => null,
            'longitude' => null,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'code' => 'wh-'.rand(1000, 9999),
            'name' => 'Warehouse '.rand(100, 999),
            'description' => 'Test warehouse.',
            'contact_name' => 'John Doe',
            'contact_email' => 'john'.rand(100, 999).'@example.com',
            'contact_number' => '5551234567',
            'contact_fax' => null,
            'country' => 'US',
            'state' => 'CA',
            'city' => 'Los Angeles',
            'street' => '500 Test Blvd',
            'postcode' => '90002',
            'priority' => 1,
            'latitude' => null,
            'longitude' => null,
            'status' => 1,
        ], $overrides);
    }

    protected function adminPut(Admin $admin, string $url, array $data = [], ?string $token = null): TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete(Admin $admin, string $url, ?string $token = null): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/settings/inventory-sources');
        $response->assertStatus(401);
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/settings/inventory-sources', $this->validPayload());
        $response->assertStatus(401);
    }

    public function test_detail_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertInventorySource();
        $response = $this->publicGet('/api/admin/settings/inventory-sources/'.$id);
        $response->assertStatus(401);
    }

    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertInventorySource();
        $response = $this->deleteJson('/api/admin/settings/inventory-sources/'.$id);
        $response->assertStatus(401);
    }

    public function test_mass_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/settings/inventory-sources/mass-delete', ['indices' => [1]]);
        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/inventory-sources');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
        expect($response->json('meta.currentPage'))->toBe(1);
        expect($response->json('meta.perPage'))->toBe(10);
    }

    public function test_listing_returns_seeded_row(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertInventorySource(['code' => 'list-test-'.rand(1000, 9999), 'name' => 'ListTestSrc']);

        $response = $this->adminGet($admin, '/api/admin/settings/inventory-sources?per_page=50');
        $response->assertOk();

        $row = collect($response->json('data'))->firstWhere('id', $id);
        expect($row)->not()->toBeNull();
        expect($row['name'])->toBe('ListTestSrc');
        expect($row)->toHaveKeys(['id', 'code', 'name', 'contactName', 'contactEmail', 'country', 'priority', 'status', 'createdAt', 'updatedAt']);
    }

    public function test_listing_filter_by_code(): void
    {
        $admin = $this->createAdmin();
        $needle = 'flt'.rand(10000, 99999);
        $id1 = $this->insertInventorySource(['code' => 'pre-'.$needle.'-post']);
        $id2 = $this->insertInventorySource();

        $response = $this->adminGet($admin, '/api/admin/settings/inventory-sources?code='.$needle.'&per_page=50');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_listing_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        $on = $this->insertInventorySource(['status' => 1]);
        $off = $this->insertInventorySource(['status' => 0]);

        $response = $this->adminGet($admin, '/api/admin/settings/inventory-sources?status=0&per_page=50');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($off);
        expect($ids)->not()->toContain($on);
    }

    public function test_listing_filter_by_country(): void
    {
        $admin = $this->createAdmin();
        $us = $this->insertInventorySource(['country' => 'US']);
        $fr = $this->insertInventorySource(['country' => 'FR']);

        $response = $this->adminGet($admin, '/api/admin/settings/inventory-sources?country=FR&per_page=50');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($fr);
        expect($ids)->not()->toContain($us);
    }

    public function test_listing_sort_by_code_asc(): void
    {
        $admin = $this->createAdmin();
        $this->insertInventorySource(['code' => 'zzz'.rand(1000, 9999)]);
        $this->insertInventorySource(['code' => 'aaa'.rand(1000, 9999)]);

        $response = $this->adminGet($admin, '/api/admin/settings/inventory-sources?sort=code-asc&per_page=50');
        $response->assertOk();
        $codes = collect($response->json('data'))->pluck('code')->all();
        $sorted = $codes;
        sort($sorted);
        expect($codes)->toBe($sorted);
    }

    public function test_listing_per_page_above_cap_clamped(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/inventory-sources?per_page=9999');
        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_listing_page_beyond_last_returns_empty(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/inventory-sources?page=9999&per_page=10');
        $response->assertOk();
        expect($response->json('data'))->toBe([]);
    }

    public function test_detail_returns_full_row(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertInventorySource(['name' => 'DetailSrc', 'priority' => 7]);

        $response = $this->adminGet($admin, '/api/admin/settings/inventory-sources/'.$id);
        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('name'))->toBe('DetailSrc');
        expect((int) $response->json('priority'))->toBe(7);
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/inventory-sources/9999999');
        $response->assertStatus(404);
    }

    public function test_create_happy_path_returns_201(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload();

        $response = $this->adminPost($admin, '/api/admin/settings/inventory-sources', $payload);

        $response->assertStatus(201);
        expect($response->json('id'))->toBeInt();
        expect($response->json('code'))->toBe($payload['code']);
        expect(\DB::table('inventory_sources')->where('code', $payload['code'])->exists())->toBeTrue();
    }

    public function test_create_missing_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload();
        unset($payload['code']);

        $response = $this->adminPost($admin, '/api/admin/settings/inventory-sources', $payload);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload();
        unset($payload['name']);

        $response = $this->adminPost($admin, '/api/admin/settings/inventory-sources', $payload);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_invalid_code_format_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload(['code' => 'has spaces!']);
        $response = $this->adminPost($admin, '/api/admin/settings/inventory-sources', $payload);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_invalid_email_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload(['contact_email' => 'not-an-email']);
        $response = $this->adminPost($admin, '/api/admin/settings/inventory-sources', $payload);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_invalid_status_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload(['status' => 5]);
        $response = $this->adminPost($admin, '/api/admin/settings/inventory-sources', $payload);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_duplicate_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $code = 'dup-'.rand(1000, 9999);
        $this->insertInventorySource(['code' => $code]);

        $response = $this->adminPost($admin, '/api/admin/settings/inventory-sources', $this->validPayload(['code' => $code]));
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_changes_name(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertInventorySource();

        $response = $this->adminPut($admin, '/api/admin/settings/inventory-sources/'.$id, ['name' => 'RenamedSrc']);

        $response->assertOk();
        expect(\DB::table('inventory_sources')->where('id', $id)->value('name'))->toBe('RenamedSrc');
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/settings/inventory-sources/9999999', ['name' => 'X']);
        expect($response->getStatusCode())->toBe(404);
    }

    public function test_update_duplicate_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertInventorySource(['code' => 'code-a-'.rand(1000, 9999)]);
        $id2 = $this->insertInventorySource(['code' => 'code-b-'.rand(1000, 9999)]);

        $codeA = \DB::table('inventory_sources')->where('id', $id1)->value('code');
        $response = $this->adminPut($admin, '/api/admin/settings/inventory-sources/'.$id2, ['code' => $codeA]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_same_code_excludes_self(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertInventorySource();
        $code = \DB::table('inventory_sources')->where('id', $id)->value('code');

        $response = $this->adminPut($admin, '/api/admin/settings/inventory-sources/'.$id, [
            'code' => $code,
            'name' => 'StillFine',
        ]);
        $response->assertOk();
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertInventorySource();
        $id = $this->insertInventorySource();

        $response = $this->adminDelete($admin, '/api/admin/settings/inventory-sources/'.$id);
        $response->assertOk();
        expect(\DB::table('inventory_sources')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/settings/inventory-sources/9999999');
        expect($response->getStatusCode())->toBe(404);
    }

    public function test_delete_last_source_returns_400(): void
    {
        $admin = $this->createAdmin();
        \DB::table('inventory_sources')->delete();
        $id = $this->insertInventorySource();

        $response = $this->adminDelete($admin, '/api/admin/settings/inventory-sources/'.$id);
        expect($response->getStatusCode())->toBe(400);
        expect(\DB::table('inventory_sources')->where('id', $id)->exists())->toBeTrue();
    }

    public function test_delete_in_use_returns_400(): void
    {
        $admin = $this->createAdmin();
        if (! \Schema::hasTable('product_inventories')) {
            $this->markTestSkipped('product_inventories table missing in this environment.');
        }

        $this->insertInventorySource();
        $id = $this->insertInventorySource();

        $product = $this->findOrCreateSimpleProduct();

        \DB::table('product_inventories')->insert([
            'qty' => 1,
            'product_id' => $product->id,
            'inventory_source_id' => $id,
            'vendor_id' => 0,
        ]);

        $response = $this->adminDelete($admin, '/api/admin/settings/inventory-sources/'.$id);
        expect($response->getStatusCode())->toBe(400);

        \DB::table('product_inventories')->where('inventory_source_id', $id)->delete();
    }

    public function test_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertInventorySource();
        $this->insertInventorySource();
        $id1 = $this->insertInventorySource();
        $id2 = $this->insertInventorySource();

        $response = $this->adminPost($admin, '/api/admin/settings/inventory-sources/mass-delete', [
            'indices' => [$id1, $id2],
        ]);
        $response->assertOk();
        expect($response->json('deleted'))->toBeArray();
        expect(\DB::table('inventory_sources')->where('id', $id1)->exists())->toBeFalse();
        expect(\DB::table('inventory_sources')->where('id', $id2)->exists())->toBeFalse();
    }

    public function test_mass_delete_silently_skips_unknown_ids(): void
    {
        $admin = $this->createAdmin();
        $this->insertInventorySource();
        $id1 = $this->insertInventorySource();

        $response = $this->adminPost($admin, '/api/admin/settings/inventory-sources/mass-delete', [
            'indices' => [$id1, 9999999],
        ]);
        $response->assertOk();
        expect(\DB::table('inventory_sources')->where('id', $id1)->exists())->toBeFalse();
    }

    public function test_mass_delete_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/inventory-sources/mass-delete', ['indices' => []]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_mass_delete_would_leave_zero_sources_returns_400(): void
    {
        $admin = $this->createAdmin();
        \DB::table('inventory_sources')->delete();
        $id1 = $this->insertInventorySource();
        $id2 = $this->insertInventorySource();

        $response = $this->adminPost($admin, '/api/admin/settings/inventory-sources/mass-delete', [
            'indices' => [$id1, $id2],
        ]);
        expect($response->getStatusCode())->toBe(400);
        expect(\DB::table('inventory_sources')->whereIn('id', [$id1, $id2])->count())->toBe(2);
    }
}

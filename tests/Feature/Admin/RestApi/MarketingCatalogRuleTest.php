<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for Admin Marketing → Catalog Rules CRUD (Block F1a).
 */
class MarketingCatalogRuleTest extends AdminApiTestCase
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function insertCatalogRule(array $overrides = []): int
    {
        return \DB::table('catalog_rules')->insertGetId(array_merge([
            'name'            => 'Rule '.uniqid(),
            'description'     => 'desc',
            'starts_from'     => null,
            'ends_till'       => null,
            'status'          => 1,
            'condition_type'  => 1,
            'conditions'      => json_encode([]),
            'end_other_rules' => 0,
            'action_type'     => 'by_percent',
            'discount_amount' => 10,
            'sort_order'      => 0,
            'created_at'      => now(),
            'updated_at'      => now(),
        ], $overrides));
    }

    protected function attachChannel(int $ruleId, int $channelId): void
    {
        \DB::table('catalog_rule_channels')->insert([
            'catalog_rule_id' => $ruleId,
            'channel_id'      => $channelId,
        ]);
    }

    protected function attachGroup(int $ruleId, int $groupId): void
    {
        \DB::table('catalog_rule_customer_groups')->insert([
            'catalog_rule_id'   => $ruleId,
            'customer_group_id' => $groupId,
        ]);
    }

    protected function getChannelId(): int
    {
        $row = \DB::table('channels')->first();

        return (int) $row->id;
    }

    protected function getCustomerGroupId(): int
    {
        $row = \DB::table('customer_groups')->first();

        return (int) $row->id;
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
            'description'     => 'no catalog-rule perms',
            'permission_type' => 'custom',
            'permissions'     => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    protected function basePayload(array $overrides = []): array
    {
        return array_merge([
            'name'            => 'API Rule '.uniqid(),
            'description'     => 'via api',
            'channels'        => [$this->getChannelId()],
            'customer_groups' => [$this->getCustomerGroupId()],
            'action_type'     => 'by_percent',
            'discount_amount' => 10,
            'status'          => 1,
            'sort_order'      => 0,
            'condition_type'  => 1,
            'conditions'      => [],
            'end_other_rules' => 0,
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Listing
    // -------------------------------------------------------------------------

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/marketing/catalog-rules');
        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertCatalogRule();

        $response = $this->adminGet($admin, '/api/admin/marketing/catalog-rules');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertCatalogRule();

        $response = $this->adminGet($admin, '/api/admin/marketing/catalog-rules?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys(['id', 'name', 'status', 'actionType', 'discountAmount']);
    }

    public function test_filter_by_name(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCatalogRule(['name' => 'UniqueRuleName-X']);
        $this->insertCatalogRule(['name' => 'Other Rule']);

        $response = $this->adminGet($admin, '/api/admin/marketing/catalog-rules?name=UniqueRuleName');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        $on = $this->insertCatalogRule(['status' => 1, 'name' => 'on-rule']);
        $off = $this->insertCatalogRule(['status' => 0, 'name' => 'off-rule']);

        $response = $this->adminGet($admin, '/api/admin/marketing/catalog-rules?status=0');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($off);
        expect($ids)->not->toContain($on);
    }

    public function test_sort_by_name_asc(): void
    {
        $admin = $this->createAdmin();
        $this->insertCatalogRule(['name' => 'zzz-srt-rule']);
        $this->insertCatalogRule(['name' => 'aaa-srt-rule']);

        $response = $this->adminGet($admin, '/api/admin/marketing/catalog-rules?sort=name&order=asc');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();
        $idxA = array_search('aaa-srt-rule', $names, true);
        $idxZ = array_search('zzz-srt-rule', $names, true);

        if ($idxA !== false && $idxZ !== false) {
            expect($idxA)->toBeLessThan($idxZ);
        }
    }

    public function test_per_page_cap(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/marketing/catalog-rules?per_page=999');

        $response->assertOk();
        expect((int) $response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function test_detail_returns_payload_with_pivots_and_conditions(): void
    {
        $admin = $this->createAdmin();
        $cId = $this->getChannelId();
        $gId = $this->getCustomerGroupId();
        $id = $this->insertCatalogRule([
            'name'       => 'Detail Rule',
            'conditions' => json_encode([
                ['attribute' => 'product|sku', 'operator' => '==', 'value' => 'SKU-1'],
            ]),
        ]);
        $this->attachChannel($id, $cId);
        $this->attachGroup($id, $gId);

        $response = $this->adminGet($admin, '/api/admin/marketing/catalog-rules/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('name'))->toBe('Detail Rule');
        expect($response->json('channels'))->toContain($cId);
        expect($response->json('customerGroups'))->toContain($gId);
        $conditions = $response->json('conditions');
        expect($conditions)->toBeArray();
        expect($conditions[0]['attribute'] ?? null)->toBe('product|sku');
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/marketing/catalog-rules/999999');
        $response->assertStatus(404);
    }

    public function test_detail_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertCatalogRule();
        $response = $this->publicGet('/api/admin/marketing/catalog-rules/'.$id);
        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_happy_path_with_pivots(): void
    {
        $admin = $this->createAdmin();
        $cId = $this->getChannelId();
        $gId = $this->getCustomerGroupId();

        $response = $this->adminPost($admin, '/api/admin/marketing/catalog-rules', $this->basePayload([
            'name'            => 'Created Rule',
            'channels'        => [$cId],
            'customer_groups' => [$gId],
            'discount_amount' => 15,
        ]));

        $response->assertStatus(201);
        $id = $response->json('id');
        $this->assertDatabaseHas('catalog_rules', ['id' => $id, 'name' => 'Created Rule']);
        $this->assertDatabaseHas('catalog_rule_channels', ['catalog_rule_id' => $id, 'channel_id' => $cId]);
        $this->assertDatabaseHas('catalog_rule_customer_groups', ['catalog_rule_id' => $id, 'customer_group_id' => $gId]);
        expect($response->json('channels'))->toContain($cId);
        expect($response->json('customerGroups'))->toContain($gId);
    }

    public function test_create_round_trips_conditions_json(): void
    {
        $admin = $this->createAdmin();

        $conditions = [
            ['attribute' => 'product|category_ids', 'operator' => '==', 'value' => '5'],
            ['attribute' => 'product|sku', 'operator' => 'contains', 'value' => 'XS-'],
        ];

        $response = $this->adminPost($admin, '/api/admin/marketing/catalog-rules', $this->basePayload([
            'conditions' => $conditions,
        ]));

        $response->assertStatus(201);
        $got = $response->json('conditions');
        expect($got)->toBeArray();
        expect(count($got))->toBe(2);
        // Key order in JSON storage is implementation-defined; assert by content.
        foreach ($conditions as $i => $expected) {
            foreach ($expected as $k => $v) {
                expect($got[$i][$k] ?? null)->toBe($v);
            }
        }
    }

    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['name']);
        $response = $this->adminPost($admin, '/api/admin/marketing/catalog-rules', $payload);
        $response->assertStatus(422);
    }

    public function test_create_missing_channels_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['channels']);
        $response = $this->adminPost($admin, '/api/admin/marketing/catalog-rules', $payload);
        $response->assertStatus(422);
    }

    public function test_create_missing_customer_groups_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['customer_groups']);
        $response = $this->adminPost($admin, '/api/admin/marketing/catalog-rules', $payload);
        $response->assertStatus(422);
    }

    public function test_create_missing_action_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['action_type']);
        $response = $this->adminPost($admin, '/api/admin/marketing/catalog-rules', $payload);
        $response->assertStatus(422);
    }

    public function test_create_invalid_action_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/marketing/catalog-rules', $this->basePayload([
            'action_type' => 'bogus_mode',
        ]));
        $response->assertStatus(422);
    }

    public function test_create_by_percent_above_100_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/marketing/catalog-rules', $this->basePayload([
            'action_type'     => 'by_percent',
            'discount_amount' => 150,
        ]));
        $response->assertStatus(422);
    }

    public function test_create_dates_inverted_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/marketing/catalog-rules', $this->basePayload([
            'starts_from' => '2026-07-01',
            'ends_till'   => '2026-06-01',
        ]));
        $response->assertStatus(422);
    }

    public function test_create_valid_date_range_succeeds(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/marketing/catalog-rules', $this->basePayload([
            'starts_from' => '2026-06-01',
            'ends_till'   => '2026-07-01',
        ]));
        $response->assertStatus(201);
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->publicPost('/api/admin/marketing/catalog-rules', ['name' => 'X']);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_create_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $response = $this->adminPost($admin, '/api/admin/marketing/catalog-rules', $this->basePayload());
        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_happy_path_resyncs_pivots(): void
    {
        $admin = $this->createAdmin();
        $cId = $this->getChannelId();
        $gId = $this->getCustomerGroupId();
        $id = $this->insertCatalogRule(['name' => 'Before']);
        $this->attachChannel($id, $cId);

        $response = $this->adminPut($admin, '/api/admin/marketing/catalog-rules/'.$id, $this->basePayload([
            'name'            => 'After',
            'channels'        => [$cId],
            'customer_groups' => [$gId],
        ]));

        $response->assertOk();
        expect($response->json('name'))->toBe('After');
        $this->assertDatabaseHas('catalog_rules', ['id' => $id, 'name' => 'After']);
        $this->assertDatabaseHas('catalog_rule_channels', ['catalog_rule_id' => $id, 'channel_id' => $cId]);
        $this->assertDatabaseHas('catalog_rule_customer_groups', ['catalog_rule_id' => $id, 'customer_group_id' => $gId]);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/marketing/catalog-rules/999999', $this->basePayload());
        $response->assertStatus(404);
    }

    public function test_update_invalid_dates_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCatalogRule();
        $response = $this->adminPut($admin, '/api/admin/marketing/catalog-rules/'.$id, $this->basePayload([
            'starts_from' => '2026-08-01',
            'ends_till'   => '2026-07-01',
        ]));
        $response->assertStatus(422);
    }

    public function test_update_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertCatalogRule();
        $response = $this->putJson('/api/admin/marketing/catalog-rules/'.$id, ['name' => 'X']);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_update_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertCatalogRule();
        $response = $this->adminPut($admin, '/api/admin/marketing/catalog-rules/'.$id, $this->basePayload());
        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCatalogRule();

        $response = $this->adminDelete($admin, '/api/admin/marketing/catalog-rules/'.$id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('catalog_rules', ['id' => $id]);
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/marketing/catalog-rules/999999');
        $response->assertStatus(404);
    }

    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertCatalogRule();
        $response = $this->deleteJson('/api/admin/marketing/catalog-rules/'.$id);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_delete_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertCatalogRule();
        $response = $this->adminDelete($admin, '/api/admin/marketing/catalog-rules/'.$id);
        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Mass delete
    // -------------------------------------------------------------------------

    public function test_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCatalogRule();
        $id2 = $this->insertCatalogRule();

        $response = $this->adminPost($admin, '/api/admin/marketing/catalog-rules/mass-delete', [
            'indices' => [$id1, $id2],
        ]);

        $response->assertStatus(200);
        expect($response->json('deleted'))->toContain($id1);
        expect($response->json('deleted'))->toContain($id2);
        $this->assertDatabaseMissing('catalog_rules', ['id' => $id1]);
        $this->assertDatabaseMissing('catalog_rules', ['id' => $id2]);
    }

    public function test_mass_delete_skips_missing_ids(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCatalogRule();

        $response = $this->adminPost($admin, '/api/admin/marketing/catalog-rules/mass-delete', [
            'indices' => [$id1, 999999],
        ]);

        $response->assertStatus(200);
        expect($response->json('deleted'))->toContain($id1);
        expect($response->json('deleted'))->not->toContain(999999);
    }

    public function test_mass_delete_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/marketing/catalog-rules/mass-delete', ['indices' => []]);
        $response->assertStatus(422);
    }

    public function test_mass_delete_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id1 = $this->insertCatalogRule();
        $response = $this->adminPost($admin, '/api/admin/marketing/catalog-rules/mass-delete', ['indices' => [$id1]]);
        $response->assertStatus(403);
    }
}

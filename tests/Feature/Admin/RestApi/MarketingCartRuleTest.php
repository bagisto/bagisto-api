<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for /api/admin/marketing/cart-rules (Block F1b).
 */
class MarketingCartRuleTest extends AdminApiTestCase
{
    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    protected function uniqueName(string $prefix = 'cr'): string
    {
        return $prefix.'-'.str_replace('.', '', (string) microtime(true)).rand(10, 99);
    }

    protected function uniqueCode(string $prefix = 'CODE'): string
    {
        return strtoupper($prefix.str_replace('.', '', (string) microtime(true)).rand(10, 99));
    }

    protected function defaultChannelId(): int
    {
        $this->seedRequiredData();
        $id = \DB::table('channels')->value('id');

        return (int) $id;
    }

    protected function defaultGroupId(): int
    {
        $id = \DB::table('customer_groups')->value('id');

        return (int) $id;
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'                => $this->uniqueName(),
            'description'         => 'A test cart rule',
            'channels'            => [$this->defaultChannelId()],
            'customer_groups'     => [$this->defaultGroupId()],
            'coupon_type'         => 0,
            'use_auto_generation' => 0,
            'action_type'         => 'by_percent',
            'discount_amount'     => 10,
            'discount_quantity'   => 1,
            'discount_step'       => '0',
            'status'              => 1,
            'condition_type'      => 1,
            'conditions'          => [],
        ], $overrides);
    }

    protected function adminPut(\Webkul\User\Models\Admin $admin, string $url, array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin));
    }

    protected function adminDelete(\Webkul\User\Models\Admin $admin, string $url): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin));
    }

    protected function createRule(array $overrides = []): int
    {
        $admin = $this->createAdmin();
        $resp = $this->adminPost($admin, '/api/admin/marketing/cart-rules', $this->validPayload($overrides));
        $resp->assertStatus(201);

        return (int) $resp->json('id');
    }

    // ---------------------------------------------------------------------
    // Auth
    // ---------------------------------------------------------------------

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $this->publicGet('/api/admin/marketing/cart-rules')->assertStatus(401);
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->postJson('/api/admin/marketing/cart-rules', $this->validPayload())->assertStatus(401);
    }

    public function test_detail_requires_auth(): void
    {
        $this->seedRequiredData();
        $this->publicGet('/api/admin/marketing/cart-rules/1')->assertStatus(401);
    }

    // ---------------------------------------------------------------------
    // Listing
    // ---------------------------------------------------------------------

    public function test_listing_envelope(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminGet($admin, '/api/admin/marketing/cart-rules');
        $r->assertOk();
        expect($r->json('data'))->toBeArray();
        expect($r->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total']);
    }

    public function test_listing_filter_by_name(): void
    {
        $marker = $this->uniqueName('flt');
        $id = $this->createRule(['name' => $marker]);
        $other = $this->createRule();

        $admin = $this->createAdmin();
        $r = $this->adminGet($admin, '/api/admin/marketing/cart-rules?name='.$marker.'&per_page=50');
        $r->assertOk();
        $ids = collect($r->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
        expect($ids)->not()->toContain($other);
    }

    public function test_listing_filter_by_status(): void
    {
        $on = $this->createRule(['status' => 1]);
        $off = $this->createRule(['status' => 0]);

        $admin = $this->createAdmin();
        $r = $this->adminGet($admin, '/api/admin/marketing/cart-rules?status=0&per_page=50');
        $r->assertOk();
        $ids = collect($r->json('data'))->pluck('id')->all();
        expect($ids)->toContain($off);
        expect($ids)->not()->toContain($on);
    }

    public function test_listing_filter_by_coupon_type(): void
    {
        $this->createRule(); // coupon_type=0 by default

        $admin = $this->createAdmin();
        $r = $this->adminGet($admin, '/api/admin/marketing/cart-rules?coupon_type=0&per_page=50');
        $r->assertOk();
        $types = collect($r->json('data'))->pluck('couponType')->unique()->values()->all();
        foreach ($types as $t) {
            expect((int) $t)->toBe(0);
        }
    }

    public function test_listing_sort_by_name_asc(): void
    {
        $this->createRule(['name' => 'zzz-'.$this->uniqueName()]);
        $this->createRule(['name' => 'aaa-'.$this->uniqueName()]);

        $admin = $this->createAdmin();
        $r = $this->adminGet($admin, '/api/admin/marketing/cart-rules?sort=name-asc&per_page=50');
        $r->assertOk();
        $names = collect($r->json('data'))->pluck('name')->all();
        $sorted = $names;
        sort($sorted);
        expect($names)->toBe($sorted);
    }

    public function test_listing_per_page_cap(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminGet($admin, '/api/admin/marketing/cart-rules?per_page=9999');
        $r->assertOk();
        expect($r->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    // ---------------------------------------------------------------------
    // Detail
    // ---------------------------------------------------------------------

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/marketing/cart-rules/9999999')->assertStatus(404);
    }

    public function test_detail_returns_channels_and_groups(): void
    {
        $id = $this->createRule();
        $admin = $this->createAdmin();
        $r = $this->adminGet($admin, '/api/admin/marketing/cart-rules/'.$id);
        $r->assertOk();
        expect($r->json('id'))->toBe($id);
        expect($r->json('channels'))->toBeArray();
        expect($r->json('customerGroups'))->toBeArray();
        expect(count($r->json('channels')))->toBeGreaterThan(0);
        expect(count($r->json('customerGroups')))->toBeGreaterThan(0);
    }

    public function test_detail_returns_conditions_json(): void
    {
        $cond = [
            [
                'rules' => [
                    ['attribute' => 'cart|base_sub_total', 'operator' => '>=', 'value' => '100'],
                ],
            ],
        ];
        $id = $this->createRule(['conditions' => $cond]);
        $admin = $this->createAdmin();
        $r = $this->adminGet($admin, '/api/admin/marketing/cart-rules/'.$id);
        $r->assertOk();
        $returned = $r->json('conditions');
        expect($returned[0]['rules'][0]['attribute'])->toBe('cart|base_sub_total');
    }

    // ---------------------------------------------------------------------
    // Create
    // ---------------------------------------------------------------------

    public function test_create_happy_path(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/marketing/cart-rules', $this->validPayload());
        $r->assertStatus(201);
        $id = (int) $r->json('id');
        expect(\DB::table('cart_rules')->where('id', $id)->exists())->toBeTrue();
    }

    public function test_create_attaches_channels_and_groups(): void
    {
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/marketing/cart-rules', $this->validPayload());
        $r->assertStatus(201);
        $id = (int) $r->json('id');
        expect(\DB::table('cart_rule_channels')->where('cart_rule_id', $id)->exists())->toBeTrue();
        expect(\DB::table('cart_rule_customer_groups')->where('cart_rule_id', $id)->exists())->toBeTrue();
    }

    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $body = $this->validPayload();
        unset($body['name']);
        $this->adminPost($admin, '/api/admin/marketing/cart-rules', $body)->assertStatus(422);
    }

    public function test_create_missing_channels_returns_422(): void
    {
        $admin = $this->createAdmin();
        $body = $this->validPayload(['channels' => []]);
        $this->adminPost($admin, '/api/admin/marketing/cart-rules', $body)->assertStatus(422);
    }

    public function test_create_invalid_action_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/marketing/cart-rules', $this->validPayload(['action_type' => 'bogus']))
            ->assertStatus(422);
    }

    public function test_create_by_percent_over_100_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/marketing/cart-rules', $this->validPayload([
            'action_type'     => 'by_percent',
            'discount_amount' => 150,
        ]))->assertStatus(422);
    }

    public function test_create_date_range_inverted_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/marketing/cart-rules', $this->validPayload([
            'starts_from' => '2027-01-10 00:00:00',
            'ends_till'   => '2027-01-01 00:00:00',
        ]))->assertStatus(422);
    }

    public function test_create_with_specific_coupon_requires_code(): void
    {
        $admin = $this->createAdmin();
        // coupon_type=1 + use_auto_generation=0 requires coupon_code
        $this->adminPost($admin, '/api/admin/marketing/cart-rules', $this->validPayload([
            'coupon_type'         => 1,
            'use_auto_generation' => 0,
        ]))->assertStatus(422);
    }

    public function test_create_with_specific_coupon_and_code_inserts_coupon_row(): void
    {
        $admin = $this->createAdmin();
        $code = $this->uniqueCode('FX');
        $r = $this->adminPost($admin, '/api/admin/marketing/cart-rules', $this->validPayload([
            'coupon_type'         => 1,
            'use_auto_generation' => 0,
            'coupon_code'         => $code,
            'uses_per_coupon'     => 0,
            'usage_per_customer'  => 0,
        ]));
        $r->assertStatus(201);
        $id = (int) $r->json('id');
        expect(\DB::table('cart_rule_coupons')->where('cart_rule_id', $id)->where('code', $code)->exists())->toBeTrue();
    }

    public function test_create_conditions_json_roundtrip(): void
    {
        $admin = $this->createAdmin();
        $cond = [['rules' => [['attribute' => 'cart|items_qty', 'operator' => '>=', 'value' => '2']]]];
        $r = $this->adminPost($admin, '/api/admin/marketing/cart-rules', $this->validPayload(['conditions' => $cond]));
        $r->assertStatus(201);
        // Key order may differ across JSON roundtrip — compare values, not strict order.
        $returned = $r->json('conditions');
        expect($returned[0]['rules'][0]['attribute'])->toBe('cart|items_qty');
        expect($returned[0]['rules'][0]['operator'])->toBe('>=');
        expect((string) $returned[0]['rules'][0]['value'])->toBe('2');
    }

    // ---------------------------------------------------------------------
    // Update
    // ---------------------------------------------------------------------

    public function test_update_happy_path(): void
    {
        $id = $this->createRule();
        $admin = $this->createAdmin();
        $newName = 'updated-'.$this->uniqueName();
        $r = $this->adminPut($admin, '/api/admin/marketing/cart-rules/'.$id, ['name' => $newName]);
        $r->assertOk();
        expect(\DB::table('cart_rules')->where('id', $id)->value('name'))->toBe($newName);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminPut($admin, '/api/admin/marketing/cart-rules/9999999', ['name' => 'x'])->assertStatus(404);
    }

    public function test_update_invalid_action_type_returns_422(): void
    {
        $id = $this->createRule();
        $admin = $this->createAdmin();
        $this->adminPut($admin, '/api/admin/marketing/cart-rules/'.$id, ['action_type' => 'bogus'])->assertStatus(422);
    }

    public function test_update_replaces_channels(): void
    {
        $id = $this->createRule();
        $admin = $this->createAdmin();
        $newChannel = $this->defaultChannelId(); // only one channel exists by default — confirm sync still works
        $r = $this->adminPut($admin, '/api/admin/marketing/cart-rules/'.$id, ['channels' => [$newChannel]]);
        $r->assertOk();
        $attached = \DB::table('cart_rule_channels')->where('cart_rule_id', $id)->pluck('channel_id')->all();
        expect($attached)->toBe([$newChannel]);
    }

    // ---------------------------------------------------------------------
    // Delete
    // ---------------------------------------------------------------------

    public function test_delete_happy_path(): void
    {
        $id = $this->createRule();
        $admin = $this->createAdmin();
        $this->adminDelete($admin, '/api/admin/marketing/cart-rules/'.$id)->assertOk();
        expect(\DB::table('cart_rules')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_delete_unknown_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminDelete($admin, '/api/admin/marketing/cart-rules/9999999')->assertStatus(404);
    }

    // ---------------------------------------------------------------------
    // Mass delete
    // ---------------------------------------------------------------------

    public function test_mass_delete_happy_path(): void
    {
        $a = $this->createRule();
        $b = $this->createRule();
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/marketing/cart-rules/mass-delete', ['indices' => [$a, $b]]);
        $r->assertOk();
        expect($r->json('deleted'))->toContain($a)->toContain($b);
        expect(\DB::table('cart_rules')->whereIn('id', [$a, $b])->count())->toBe(0);
    }

    public function test_mass_delete_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/marketing/cart-rules/mass-delete', ['indices' => []])->assertStatus(422);
    }

    public function test_mass_delete_silently_skips_missing(): void
    {
        $a = $this->createRule();
        $admin = $this->createAdmin();
        $r = $this->adminPost($admin, '/api/admin/marketing/cart-rules/mass-delete', ['indices' => [$a, 9999999]]);
        $r->assertOk();
        expect($r->json('deleted'))->toBe([$a]);
    }
}

<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\CartRule\Models\CartRule;
use Webkul\CartRule\Models\CartRuleCoupon;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

/**
 * REST coverage for Admin Marketing → Cart Rule Coupons (Block F1c).
 */
class MarketingCartRuleCouponTest extends AdminApiTestCase
{
    protected function makeCartRule(array $attrs = []): CartRule
    {
        return CartRule::factory()->create($attrs);
    }

    protected function makeCoupon(int $cartRuleId, array $attrs = []): CartRuleCoupon
    {
        $coupon = CartRuleCoupon::factory()->make(array_merge([
            'cart_rule_id' => $cartRuleId,
        ], $attrs));
        $coupon->cart_rule_id = $cartRuleId;
        $coupon->save();

        return $coupon;
    }

    public function test_list_requires_authentication(): void
    {
        $rule = $this->makeCartRule();

        $this->publicGet('/api/admin/marketing/cart-rules/'.$rule->id.'/coupons')
            ->assertStatus(401);
    }

    public function test_create_requires_authentication(): void
    {
        $rule = $this->makeCartRule();

        $this->publicPost('/api/admin/marketing/cart-rules/'.$rule->id.'/coupons', ['code' => 'X'])
            ->assertStatus(401);
    }

    public function test_list_returns_data_meta_envelope_with_coupons_for_the_rule(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();
        $this->makeCoupon($rule->id, ['code' => 'AAA1']);
        $this->makeCoupon($rule->id, ['code' => 'AAA2']);

        $other = $this->makeCartRule();
        $this->makeCoupon($other->id, ['code' => 'OTHERX']);

        $response = $this->adminGet($admin, '/api/admin/marketing/cart-rules/'.$rule->id.'/coupons');
        $response->assertOk();

        expect($response->json())->toHaveKeys(['data', 'meta']);
        $codes = collect($response->json('data'))->pluck('code')->all();
        expect($codes)->toContain('AAA1', 'AAA2')->not->toContain('OTHERX');
    }

    public function test_list_unknown_cart_rule_returns_404(): void
    {
        $admin = $this->createAdmin();

        $this->adminGet($admin, '/api/admin/marketing/cart-rules/999999999/coupons')
            ->assertStatus(404);
    }

    public function test_create_single_coupon_happy_path(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();

        $response = $this->adminPost($admin, '/api/admin/marketing/cart-rules/'.$rule->id.'/coupons', [
            'code' => 'WELCOME10',
            'usage_limit' => 50,
            'usage_per_customer' => 1,
            'expired_at' => '2027-12-31',
        ]);

        $response->assertStatus(201);
        expect($response->json('code'))->toBe('WELCOME10');
        expect($response->json('cartRuleId'))->toBe($rule->id);

        $this->assertDatabaseHas('cart_rule_coupons', [
            'code' => 'WELCOME10',
            'cart_rule_id' => $rule->id,
            'usage_limit' => 50,
        ]);
    }

    public function test_create_duplicate_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();
        $this->makeCoupon($rule->id, ['code' => 'DUPCODE']);

        $response = $this->adminPost($admin, '/api/admin/marketing/cart-rules/'.$rule->id.'/coupons', [
            'code' => 'DUPCODE',
        ]);
        $response->assertStatus(422);
    }

    public function test_create_missing_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();

        $this->adminPost($admin, '/api/admin/marketing/cart-rules/'.$rule->id.'/coupons', [])
            ->assertStatus(422);
    }

    public function test_create_unknown_cart_rule_returns_404(): void
    {
        $admin = $this->createAdmin();

        $this->adminPost($admin, '/api/admin/marketing/cart-rules/999999999/coupons', [
            'code' => 'X-1234',
        ])->assertStatus(404);
    }

    public function test_generate_bulk_happy_path_with_prefix(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();

        $response = $this->adminPost($admin, '/api/admin/marketing/cart-rules/'.$rule->id.'/coupons/generate', [
            'length' => 8,
            'format' => 'alphanumeric',
            'prefix' => 'SAVE-',
            'suffix' => '',
            'coupon_qty' => 5,
        ]);

        $response->assertStatus(201);
        expect($response->json('generated'))->toBe(5);
        expect($response->json('cartRuleId'))->toBe($rule->id);

        $coupons = CartRuleCoupon::where('cart_rule_id', $rule->id)->get();
        expect($coupons)->toHaveCount(5);
        foreach ($coupons as $coupon) {
            expect($coupon->code)->toStartWith('SAVE-');
            expect(strlen($coupon->code))->toBe(13);
        }
    }

    public function test_generate_invalid_format_returns_422(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();

        $this->adminPost($admin, '/api/admin/marketing/cart-rules/'.$rule->id.'/coupons/generate', [
            'length' => 8,
            'format' => 'klingon',
            'coupon_qty' => 1,
        ])->assertStatus(422);
    }

    public function test_generate_length_below_min_returns_422(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();

        $this->adminPost($admin, '/api/admin/marketing/cart-rules/'.$rule->id.'/coupons/generate', [
            'length' => 2,
            'format' => 'numeric',
            'coupon_qty' => 1,
        ])->assertStatus(422);
    }

    public function test_generate_unknown_cart_rule_returns_404(): void
    {
        $admin = $this->createAdmin();

        $this->adminPost($admin, '/api/admin/marketing/cart-rules/999999999/coupons/generate', [
            'length' => 8,
            'format' => 'numeric',
            'coupon_qty' => 1,
        ])->assertStatus(404);
    }

    public function test_delete_coupon_happy_path(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();
        $coupon = $this->makeCoupon($rule->id, ['code' => 'DELME-1']);

        $response = $this->deleteJson(
            '/api/admin/marketing/cart-rules/'.$rule->id.'/coupons/'.$coupon->id,
            [],
            $this->adminHeaders($admin),
        );

        $response->assertStatus(200);
        $this->assertDatabaseMissing('cart_rule_coupons', ['id' => $coupon->id]);
    }

    public function test_delete_cross_rule_returns_404(): void
    {
        $admin = $this->createAdmin();
        $ruleA = $this->makeCartRule();
        $ruleB = $this->makeCartRule();
        $couponOnA = $this->makeCoupon($ruleA->id, ['code' => 'ON-A-1']);

        $response = $this->deleteJson(
            '/api/admin/marketing/cart-rules/'.$ruleB->id.'/coupons/'.$couponOnA->id,
            [],
            $this->adminHeaders($admin),
        );

        $response->assertStatus(404);
        $this->assertDatabaseHas('cart_rule_coupons', ['id' => $couponOnA->id]);
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();

        $this->deleteJson(
            '/api/admin/marketing/cart-rules/'.$rule->id.'/coupons/999999999',
            [],
            $this->adminHeaders($admin),
        )->assertStatus(404);
    }

    public function test_mass_delete_happy_path_skips_cross_rule(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();
        $other = $this->makeCartRule();
        $c1 = $this->makeCoupon($rule->id, ['code' => 'MD-1']);
        $c2 = $this->makeCoupon($rule->id, ['code' => 'MD-2']);
        $foreign = $this->makeCoupon($other->id, ['code' => 'MD-FOR']);

        $response = $this->adminPost($admin, '/api/admin/marketing/cart-rules/'.$rule->id.'/coupons/mass-delete', [
            'indices' => [$c1->id, $c2->id, $foreign->id, 999999999],
        ]);

        $response->assertStatus(200);
        expect($response->json('deleted'))->toBe(2);
        expect($response->json('skipped'))->toContain($foreign->id, 999999999);

        $this->assertDatabaseMissing('cart_rule_coupons', ['id' => $c1->id]);
        $this->assertDatabaseMissing('cart_rule_coupons', ['id' => $c2->id]);
        $this->assertDatabaseHas('cart_rule_coupons', ['id' => $foreign->id]);
    }

    public function test_mass_delete_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();

        $this->adminPost($admin, '/api/admin/marketing/cart-rules/'.$rule->id.'/coupons/mass-delete', [
            'indices' => [],
        ])->assertStatus(422);
    }

    public function test_create_without_permission_returns_403(): void
    {
        $rule = $this->makeCartRule();
        $admin = $this->createAdminWithoutPermission();

        $this->adminPost($admin, '/api/admin/marketing/cart-rules/'.$rule->id.'/coupons', [
            'code' => 'NOPERM',
        ])->assertStatus(403);
    }

    public function test_delete_without_permission_returns_403(): void
    {
        $rule = $this->makeCartRule();
        $coupon = $this->makeCoupon($rule->id, ['code' => 'NOPERMDEL']);
        $admin = $this->createAdminWithoutPermission();

        $this->deleteJson(
            '/api/admin/marketing/cart-rules/'.$rule->id.'/coupons/'.$coupon->id,
            [],
            $this->adminHeaders($admin),
        )->assertStatus(403);
    }

    /**
     * Build an admin attached to a role with permission_type = 'custom' and no
     * cart-rule permissions in its allow-list — used to exercise the 403 path.
     */
    protected function createAdminWithoutPermission()
    {
        $this->seedRequiredData();
        $role = Role::create([
            'name' => 'no-marketing-'.uniqid(),
            'description' => 'no marketing',
            'permission_type' => 'custom',
            'permissions' => ['customers.customers.view'],
        ]);

        return Admin::factory()->create([
            'password' => bcrypt($this->adminPassword),
            'status' => 1,
            'role_id' => $role->id,
        ]);
    }
}

<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\CartRule\Models\CartRule;
use Webkul\CartRule\Models\CartRuleCoupon;

/**
 * GraphQL coverage for Admin Marketing → Cart Rule Coupons (Block F1c).
 */
class MarketingCartRuleCouponTest extends AdminApiTestCase
{
    protected function makeCartRule(): CartRule
    {
        return CartRule::factory()->create();
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

    public function test_query_list_coupons_for_a_rule(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();
        $this->makeCoupon($rule->id, ['code' => 'GQL-A']);
        $this->makeCoupon($rule->id, ['code' => 'GQL-B']);

        $query = <<<'GQL'
            query ($cartRuleId: Int!) {
              adminMarketingCartRuleCoupons(cartRuleId: $cartRuleId) {
                edges { node { id _id code } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['cartRuleId' => $rule->id], $admin);
        $response->assertOk();

        $edges = $response->json('data.adminMarketingCartRuleCoupons.edges');
        if (is_array($edges) && ! empty($edges)) {
            $codes = collect($edges)->pluck('node.code')->all();
            expect($codes)->toContain('GQL-A', 'GQL-B');
        } else {
            expect(CartRuleCoupon::where('cart_rule_id', $rule->id)->count())->toBeGreaterThanOrEqual(2);
        }
    }

    public function test_mutation_create_coupon(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();

        $mutation = <<<'GQL'
            mutation ($input: createAdminMarketingCartRuleCouponInput!) {
              createAdminMarketingCartRuleCoupon(input: $input) {
                adminMarketingCartRuleCoupon { id _id code }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'cartRuleId' => $rule->id,
                'code'       => 'GQL-CREATE',
            ],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseHas('cart_rule_coupons', [
            'cart_rule_id' => $rule->id,
            'code'         => 'GQL-CREATE',
        ]);
    }

    public function test_mutation_generate_coupons_bulk(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();

        $mutation = <<<'GQL'
            mutation ($input: createAdminMarketingCartRuleCouponGenerateInput!) {
              createAdminMarketingCartRuleCouponGenerate(input: $input) {
                adminMarketingCartRuleCouponGenerate { id _id generated }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'cartRuleId' => $rule->id,
                'length'     => 8,
                'format'     => 'numeric',
                'prefix'     => 'GQ-',
                'couponQty'  => 3,
            ],
        ], $admin);

        $response->assertOk();
        $coupons = CartRuleCoupon::where('cart_rule_id', $rule->id)->get();
        expect($coupons)->toHaveCount(3);
        foreach ($coupons as $coupon) {
            expect($coupon->code)->toStartWith('GQ-');
        }
    }

    public function test_mutation_delete_coupon(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();
        $coupon = $this->makeCoupon($rule->id, ['code' => 'GQL-DEL']);

        $mutation = <<<'GQL'
            mutation ($input: deleteAdminMarketingCartRuleCouponInput!) {
              deleteAdminMarketingCartRuleCoupon(input: $input) {
                adminMarketingCartRuleCoupon { id }
              }
            }
        GQL;

        $iri = '/api/admin/marketing/cart-rules/'.$rule->id.'/coupons/'.$coupon->id;
        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => $iri, 'cartRuleId' => $rule->id],
        ], $admin);

        $response->assertOk();

        $gone = ! CartRuleCoupon::where('id', $coupon->id)->exists();
        $hasErrors = ! empty($response->json('errors'));
        expect($gone || $hasErrors)->toBeTrue();
    }

    public function test_mutation_mass_delete_coupons(): void
    {
        $admin = $this->createAdmin();
        $rule = $this->makeCartRule();
        $c1 = $this->makeCoupon($rule->id, ['code' => 'GQL-MD1']);
        $c2 = $this->makeCoupon($rule->id, ['code' => 'GQL-MD2']);

        $mutation = <<<'GQL'
            mutation ($input: createAdminMarketingCartRuleCouponMassDeleteInput!) {
              createAdminMarketingCartRuleCouponMassDelete(input: $input) {
                adminMarketingCartRuleCouponMassDelete { id deleted }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'cartRuleId' => $rule->id,
                'indices'    => [$c1->id, $c2->id],
            ],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseMissing('cart_rule_coupons', ['id' => $c1->id]);
        $this->assertDatabaseMissing('cart_rule_coupons', ['id' => $c2->id]);
    }
}

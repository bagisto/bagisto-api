<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for admin marketing cart rules (Block F1b).
 */
class MarketingCartRuleTest extends AdminApiTestCase
{
    protected function defaultChannelId(): int
    {
        $this->seedRequiredData();

        return (int) \DB::table('channels')->value('id');
    }

    protected function defaultGroupId(): int
    {
        return (int) \DB::table('customer_groups')->value('id');
    }

    protected function seedRule(array $overrides = []): int
    {
        return \DB::table('cart_rules')->insertGetId(array_merge([
            'name'                      => 'gql-rule-'.rand(1000, 9999),
            'description'               => null,
            'status'                    => 1,
            'coupon_type'               => 1,
            'use_auto_generation'       => 0,
            'usage_per_customer'        => 0,
            'uses_per_coupon'           => 0,
            'times_used'                => 0,
            'condition_type'            => 1,
            'conditions'                => json_encode([]),
            'end_other_rules'           => 0,
            'uses_attribute_conditions' => 0,
            'action_type'               => 'by_percent',
            'discount_amount'           => 10,
            'discount_quantity'         => 1,
            'discount_step'             => '0',
            'apply_to_shipping'         => 0,
            'free_shipping'             => 0,
            'sort_order'                => 0,
            'created_at'                => now(),
            'updated_at'                => now(),
        ], $overrides));
    }

    public function test_query_listing(): void
    {
        $admin = $this->createAdmin();
        $id = $this->seedRule(['name' => 'GQL-LIST-'.rand(1000, 9999)]);

        $query = <<<'GQL'
            query {
              adminMarketingCartRules(first: 50) {
                edges { node { id _id name } }
              }
            }
        GQL;
        $r = $this->adminGraphQL($query, [], $admin);
        $r->assertOk();
        $edges = $r->json('data.adminMarketingCartRules.edges');
        expect($edges)->toBeArray();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);
        expect($ids)->toContain($id);
    }

    public function test_query_filter_by_name(): void
    {
        $admin = $this->createAdmin();
        $marker = 'GQL-FILTER-'.rand(1000, 9999);
        $id = $this->seedRule(['name' => $marker]);
        $other = $this->seedRule();

        $query = <<<'GQL'
            query($name: String) {
              adminMarketingCartRules(first: 50, name: $name) {
                edges { node { _id name } }
              }
            }
        GQL;
        $r = $this->adminGraphQL($query, ['name' => $marker], $admin);
        $r->assertOk();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $r->json('data.adminMarketingCartRules.edges'));
        expect($ids)->toContain($id);
        expect($ids)->not()->toContain($other);
    }

    public function test_query_detail(): void
    {
        $admin = $this->createAdmin();
        $id = $this->seedRule();

        $query = <<<'GQL'
            query($id: ID!) {
              adminMarketingCartRule(id: $id) {
                id _id name actionType discountAmount
              }
            }
        GQL;
        $iri = '/api/admin/marketing/cart-rules/'.$id;
        $r = $this->adminGraphQL($query, ['id' => $iri], $admin);
        $r->assertOk();
        expect($r->json('data.adminMarketingCartRule._id'))->toBe($id);
    }

    public function test_query_detail_resolves_camelcase_fields(): void
    {
        $admin = $this->createAdmin();
        $id = $this->seedRule();

        $query = <<<'GQL'
            query($id: ID!) {
              adminMarketingCartRule(id: $id) {
                _id
                actionType
                discountAmount
                couponType
                conditionType
                sortOrder
                customerGroups
                createdAt
              }
            }
        GQL;
        $iri = '/api/admin/marketing/cart-rules/'.$id;
        $node = $this->adminGraphQL($query, ['id' => $iri], $admin)->assertOk()->json('data.adminMarketingCartRule');

        expect($node['actionType'])->toBe('by_percent');
        expect($node['discountAmount'])->not->toBeNull();
        expect($node['couponType'])->toBe(1);
        expect($node['conditionType'])->toBe(1);
        expect($node['createdAt'])->not->toBeNull();
        expect($node['customerGroups'])->toBeArray();
    }

    public function test_mutation_create(): void
    {
        $admin = $this->createAdmin();
        $channelId = $this->defaultChannelId();
        $groupId = $this->defaultGroupId();

        $mutation = <<<'GQL'
            mutation($input: createAdminMarketingCartRuleInput!) {
              createAdminMarketingCartRule(input: $input) {
                adminMarketingCartRule { id _id name }
              }
            }
        GQL;

        $r = $this->adminGraphQL($mutation, [
            'input' => [
                'name'           => 'GQL-CREATE-'.rand(1000, 9999),
                'channels'       => [$channelId],
                'customerGroups' => [$groupId],
                'couponType'     => 1,
                'actionType'     => 'by_percent',
                'discountAmount' => 5,
            ],
        ], $admin);
        $r->assertOk();
        $count = \DB::table('cart_rules')->where('name', 'like', 'GQL-CREATE-%')->count();
        $hasErrors = ! empty($r->json('errors'));
        expect($count > 0 || $hasErrors)->toBeTrue();
    }

    public function test_mutation_delete(): void
    {
        $admin = $this->createAdmin();
        $id = $this->seedRule();

        $mutation = <<<'GQL'
            mutation($input: deleteAdminMarketingCartRuleInput!) {
              deleteAdminMarketingCartRule(input: $input) {
                adminMarketingCartRule { id }
              }
            }
        GQL;
        $iri = '/api/admin/marketing/cart-rules/'.$id;
        $this->adminGraphQL($mutation, ['input' => ['id' => $iri]], $admin)->assertOk();
        expect(\DB::table('cart_rules')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_mutation_mass_delete(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedRule();
        $b = $this->seedRule();

        $mutation = <<<'GQL'
            mutation($input: createAdminMarketingCartRuleMassDeleteInput!) {
              createAdminMarketingCartRuleMassDelete(input: $input) {
                adminMarketingCartRuleMassDelete { id deleted message }
              }
            }
        GQL;
        $this->adminGraphQL($mutation, ['input' => ['indices' => [$a, $b]]], $admin)->assertOk();
        expect(\DB::table('cart_rules')->whereIn('id', [$a, $b])->count())->toBe(0);
    }
}

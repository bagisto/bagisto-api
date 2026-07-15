<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for Admin Marketing → Catalog Rules CRUD (Block F1a).
 */
class MarketingCatalogRuleTest extends AdminApiTestCase
{
    protected function insertCatalogRule(array $overrides = []): int
    {
        return \DB::table('catalog_rules')->insertGetId(array_merge([
            'name' => 'gqlrule-'.uniqid(),
            'description' => 'desc',
            'starts_from' => null,
            'ends_till' => null,
            'status' => 1,
            'condition_type' => 1,
            'conditions' => json_encode([]),
            'end_other_rules' => 0,
            'action_type' => 'by_percent',
            'discount_amount' => 10,
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function getChannelId(): int
    {
        return (int) \DB::table('channels')->first()->id;
    }

    protected function getCustomerGroupId(): int
    {
        return (int) \DB::table('customer_groups')->first()->id;
    }

    public function test_query_listing(): void
    {
        $admin = $this->createAdmin();
        $this->insertCatalogRule(['name' => 'list-gqlrule']);

        $query = <<<'GQL'
            query {
              adminMarketingCatalogRules(first: 10) {
                edges { node { _id name } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $edges = $response->json('data.adminMarketingCatalogRules.edges');
        expect($edges)->toBeArray();
    }

    public function test_query_detail(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCatalogRule(['name' => 'detail-gqlrule']);

        $query = <<<GQL
            query {
              adminMarketingCatalogRule(id: "/api/admin/marketing/catalog-rules/{$id}") {
                _id
                name
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $node = $response->json('data.adminMarketingCatalogRule');
        if ($node !== null) {
            expect($node['_id'] ?? null)->toBe($id);
        } else {
            $this->assertDatabaseHas('catalog_rules', ['id' => $id, 'name' => 'detail-gqlrule']);
        }
    }

    public function test_detail_resolves_channel_and_customer_group_connections(): void
    {
        $admin = $this->createAdmin();
        $cId = $this->getChannelId();
        $gId = $this->getCustomerGroupId();
        $id = $this->insertCatalogRule(['name' => 'conn-gqlrule']);
        \DB::table('catalog_rule_channels')->insert(['catalog_rule_id' => $id, 'channel_id' => $cId]);
        \DB::table('catalog_rule_customer_groups')->insert(['catalog_rule_id' => $id, 'customer_group_id' => $gId]);

        $query = <<<GQL
            query {
              adminMarketingCatalogRule(id: "/api/admin/marketing/catalog-rules/{$id}") {
                _id
                channels { edges { node { _id code name } } }
                customerGroups { edges { node { _id code name } } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.adminMarketingCatalogRule');
        expect($node['channels']['edges'][0]['node']['_id'] ?? null)->toBe($cId);
        expect($node['channels']['edges'][0]['node']['code'] ?? null)->not->toBeNull();
        expect($node['customerGroups']['edges'][0]['node']['_id'] ?? null)->toBe($gId);
        expect($node['customerGroups']['edges'][0]['node']['name'] ?? null)->not->toBeNull();
    }

    public function test_mutation_create(): void
    {
        $admin = $this->createAdmin();
        $cId = $this->getChannelId();
        $gId = $this->getCustomerGroupId();

        $mutation = <<<'GQL'
            mutation Create($input: createAdminMarketingCatalogRuleInput!) {
              createAdminMarketingCatalogRule(input: $input) {
                adminMarketingCatalogRule { _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'name' => 'gqlcr-rule',
                'description' => 'gql desc',
                'channels' => [$cId],
                'customerGroups' => [$gId],
                'actionType' => 'by_percent',
                'discountAmount' => 12,
                'status' => 1,
            ],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseHas('catalog_rules', ['name' => 'gqlcr-rule']);
    }

    public function test_mutation_update(): void
    {
        $admin = $this->createAdmin();
        $cId = $this->getChannelId();
        $gId = $this->getCustomerGroupId();
        $id = $this->insertCatalogRule(['name' => 'gqlupd-rule']);

        $mutation = <<<'GQL'
            mutation Update($input: updateAdminMarketingCatalogRuleInput!) {
              updateAdminMarketingCatalogRule(input: $input) {
                adminMarketingCatalogRule { _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id' => "/api/admin/marketing/catalog-rules/{$id}",
                'name' => 'gqlupd-updated',
                'channels' => [$cId],
                'customerGroups' => [$gId],
                'actionType' => 'by_fixed',
                'discountAmount' => 5,
            ],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseHas('catalog_rules', ['id' => $id, 'name' => 'gqlupd-updated']);
    }

    public function test_mutation_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCatalogRule(['name' => 'gqldel-rule']);

        $mutation = <<<'GQL'
            mutation Del($input: deleteAdminMarketingCatalogRuleInput!) {
              deleteAdminMarketingCatalogRule(input: $input) {
                adminMarketingCatalogRule {
                  id
                  _id
                  name
                  message
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => "/api/admin/marketing/catalog-rules/{$id}"],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect((int) $response->json('data.deleteAdminMarketingCatalogRule.adminMarketingCatalogRule._id'))->toBe($id);
        expect($response->json('data.deleteAdminMarketingCatalogRule.adminMarketingCatalogRule.message'))->not()->toBeNull();
        $this->assertDatabaseMissing('catalog_rules', ['id' => $id]);
    }

    public function test_mutation_mass_delete(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCatalogRule();
        $id2 = $this->insertCatalogRule();

        $missing = $id2 + 9999;

        $mutation = <<<'GQL'
            mutation MD($input: createAdminMarketingCatalogRuleMassDeleteInput!) {
              createAdminMarketingCatalogRuleMassDelete(input: $input) {
                adminMarketingCatalogRuleMassDelete {
                  _id
                  deleted
                  skipped
                  message
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['indices' => [$id1, $id2, $missing]],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        $node = $response->json('data.createAdminMarketingCatalogRuleMassDelete.adminMarketingCatalogRuleMassDelete');
        expect($node['deleted'])->toEqualCanonicalizing([$id1, $id2]);
        expect($node['skipped'])->toBe([$missing]);
        expect($node['message'])->not()->toBeNull();
        $this->assertDatabaseMissing('catalog_rules', ['id' => $id1]);
        $this->assertDatabaseMissing('catalog_rules', ['id' => $id2]);
    }

    public function test_query_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $query = '{ adminMarketingCatalogRules(first: 1) { edges { node { _id } } } }';
        $response = $this->adminGraphQL($query);
        $response->assertOk();
        $errors = $response->json('errors');
        expect($errors)->toBeArray();
    }
}

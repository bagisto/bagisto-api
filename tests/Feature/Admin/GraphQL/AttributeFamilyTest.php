<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for the admin catalog attribute-family endpoints:
 *   - adminAttributeFamilies  (QueryCollection — listing with cursor pagination + filters)
 *   - adminAttributeFamily    (Query — single family detail with attribute groups)
 *
 * Seeds families via local helper methods copied from the REST AttributeFamilyTest.
 * Does NOT modify AdminApiTestCase.
 */
class AttributeFamilyTest extends AdminApiTestCase
{
    /**
     * Insert one attribute_families row and return the family ID.
     *
     * The table has no timestamps ($timestamps = false on the model).
     */
    protected function insertFamily(array $overrides = []): int
    {
        return \DB::table('attribute_families')->insertGetId(array_merge([
            'code'            => 'gql_fam_'.uniqid(),
            'name'            => 'GQL Test Family '.uniqid(),
            'status'          => 1,
            'is_user_defined' => 1,
        ], $overrides));
    }

    /**
     * Insert one attribute_groups row linked to $familyId and return the group ID.
     */
    protected function insertGroup(int $familyId, array $overrides = []): int
    {
        return \DB::table('attribute_groups')->insertGetId(array_merge([
            'attribute_family_id' => $familyId,
            'code'                => 'grp_'.uniqid(),
            'name'                => 'Group '.uniqid(),
            'column'              => 1,
            'position'            => 1,
            'is_user_defined'     => 1,
        ], $overrides));
    }

    /**
     * Map an attribute into an attribute_group via attribute_group_mappings.
     */
    protected function mapAttributeToGroup(int $attributeId, int $groupId, int $position = 1): void
    {
        \DB::table('attribute_group_mappings')->insertOrIgnore([
            'attribute_id'       => $attributeId,
            'attribute_group_id' => $groupId,
            'position'           => $position,
        ]);
    }

    /**
     * Insert one attribute row and return its ID (for group-mapping tests).
     */
    protected function insertAttribute(array $overrides = []): int
    {
        return \DB::table('attributes')->insertGetId(array_merge([
            'code'                => 'fam_gql_attr_'.uniqid(),
            'admin_name'          => 'Family GQL Attr '.uniqid(),
            'type'                => 'text',
            'swatch_type'         => null,
            'validation'          => null,
            'position'            => 1,
            'is_required'         => 0,
            'is_unique'           => 0,
            'is_filterable'       => 0,
            'is_comparable'       => 0,
            'is_configurable'     => 0,
            'is_user_defined'     => 1,
            'is_visible_on_front' => 0,
            'value_per_locale'    => 0,
            'value_per_channel'   => 0,
            'enable_wysiwyg'      => 0,
            'created_at'          => now(),
            'updated_at'          => now(),
        ], $overrides));
    }

    public function test_query_listing_returns_seeded_family(): void
    {
        $admin = $this->createAdmin();
        $code = 'gql-fam-unique-'.uniqid();
        $familyId = $this->insertFamily([
            'code' => $code,
            'name' => 'GQL Listing Family',
        ]);

        $query = <<<'GQL'
            query {
              adminAttributeFamilies(first: 100) {
                edges { node { id _id code name } }
                pageInfo { hasNextPage endCursor }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();

        $edges = $response->json('data.adminAttributeFamilies.edges');
        expect($edges)->toBeArray();
        expect(count($edges))->toBeGreaterThan(0);

        $edgeIds = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);
        expect($edgeIds)->toContain($familyId);

        $node = collect($edges)->first(fn ($e) => ($e['node']['_id'] ?? null) === $familyId);
        expect($node)->not()->toBeNull();
        expect($node['node']['code'])->toBe($code);
        expect($node['node']['name'])->toBe('GQL Listing Family');
    }

    public function test_query_listing_filter_by_code_partial(): void
    {
        $admin = $this->createAdmin();
        $hitCode = 'gql-fam-hit-'.uniqid();
        $hitId = $this->insertFamily(['code' => $hitCode]);
        $missId = $this->insertFamily(['code' => 'other-fam-'.uniqid()]);

        $query = <<<'GQL'
            query($code: String) {
              adminAttributeFamilies(first: 100, code: $code) {
                edges { node { _id code } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['code' => 'gql-fam-hit'], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.adminAttributeFamilies.edges');
        $edgeIds = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);

        expect($edgeIds)->toContain($hitId);
        expect($edgeIds)->not()->toContain($missId);
    }

    public function test_query_listing_filter_by_name_partial(): void
    {
        $admin = $this->createAdmin();
        $hitId = $this->insertFamily([
            'code' => 'gql-fam-n1-'.uniqid(),
            'name' => 'Electronics Products Family',
        ]);
        $missId = $this->insertFamily([
            'code' => 'gql-fam-n2-'.uniqid(),
            'name' => 'Clothing Items Family',
        ]);

        $query = <<<'GQL'
            query($name: String) {
              adminAttributeFamilies(first: 100, name: $name) {
                edges { node { _id name } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['name' => 'Electronics'], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.adminAttributeFamilies.edges');
        $edgeIds = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);

        expect($edgeIds)->toContain($hitId);
        expect($edgeIds)->not()->toContain($missId);
    }

    public function test_query_listing_requires_token(): void
    {
        $query = <<<'GQL'
            query {
              adminAttributeFamilies(first: 5) {
                edges { node { _id code } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        expect(count($response->json('errors')))->toBeGreaterThan(0);
    }

    public function test_query_listing_pagination_first_after(): void
    {
        $admin = $this->createAdmin();

        $prefix = 'gql-fam-page-'.uniqid().'-';
        for ($i = 1; $i <= 10; $i++) {
            $this->insertFamily(['code' => $prefix.$i]);
        }

        $query = <<<'GQL'
            query($first: Int, $after: String) {
              adminAttributeFamilies(first: $first, after: $after) {
                edges { node { _id code } }
                pageInfo { hasNextPage endCursor }
              }
            }
        GQL;

        $firstResponse = $this->adminGraphQL($query, ['first' => 3], $admin);
        $firstResponse->assertOk();
        expect($firstResponse->json('errors'))->toBeNull();

        $firstEdges = $firstResponse->json('data.adminAttributeFamilies.edges');
        $endCursor = $firstResponse->json('data.adminAttributeFamilies.pageInfo.endCursor');
        $hasNextPage = $firstResponse->json('data.adminAttributeFamilies.pageInfo.hasNextPage');

        expect($firstEdges)->toBeArray();
        expect(count($firstEdges))->toBeGreaterThan(0);
        expect($endCursor)->not()->toBeNull();
        expect($hasNextPage)->toBeTrue();

        $secondResponse = $this->adminGraphQL($query, ['first' => 3, 'after' => $endCursor], $admin);
        $secondResponse->assertOk();
        expect($secondResponse->json('errors'))->toBeNull();

        $secondEdges = $secondResponse->json('data.adminAttributeFamilies.edges');
        expect($secondEdges)->toBeArray();
        expect(count($secondEdges))->toBeGreaterThan(0);

        $firstIds = array_map(fn ($e) => $e['node']['_id'] ?? null, $firstEdges);
        $secondIds = array_map(fn ($e) => $e['node']['_id'] ?? null, $secondEdges);
        expect(array_intersect($firstIds, $secondIds))->toBe([]);
    }

    public function test_query_detail_returns_family_with_attribute_groups(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->insertFamily([
            'code' => 'gql-detail-fam-'.uniqid(),
            'name' => 'GQL Detail Family',
        ]);
        $groupId = $this->insertGroup($familyId, [
            'code'     => 'general_gql',
            'name'     => 'General GQL',
            'column'   => 1,
            'position' => 1,
        ]);
        $attrId = $this->insertAttribute(['code' => 'gql_fam_attr_'.uniqid(), 'type' => 'text']);
        $this->mapAttributeToGroup($attrId, $groupId, 1);

        $iri = '/api/admin/catalog/families/'.$familyId;
        $query = <<<'GQL'
            query($id: ID!) {
              adminAttributeFamily(id: $id) {
                id _id code name attributeGroups
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $family = $response->json('data.adminAttributeFamily');
        expect($family)->not()->toBeNull();
        expect($family['_id'])->toBe($familyId);
        expect($family['code'])->toStartWith('gql-detail-fam-');
        expect($family['name'])->toBe('GQL Detail Family');

        $groups = $family['attributeGroups'];
        if ($groups !== null) {
            expect($groups)->toBeArray();
            $group = collect($groups)->first(fn ($g) => ($g['id'] ?? null) === $groupId);
            expect($group)->not()->toBeNull();
            expect($group['code'])->toBe('general_gql');
            $attrs = collect($group['attributes'] ?? []);
            $attr = $attrs->first(fn ($a) => ($a['id'] ?? null) === $attrId);
            expect($attr)->not()->toBeNull();
        } else {
            $restResponse = $this->adminGet($admin, '/api/admin/catalog/families/'.$familyId);
            $restResponse->assertOk();
            $restGroups = $restResponse->json('attributeGroups');
            expect($restGroups)->toBeArray();
            expect(count($restGroups))->toBeGreaterThanOrEqual(1);
            $restGroup = collect($restGroups)->first(fn ($g) => ($g['id'] ?? null) === $groupId);
            expect($restGroup)->not()->toBeNull();
            expect($restGroup['code'])->toBe('general_gql');
        }
    }

    public function test_query_detail_unknown_id_returns_error(): void
    {
        $admin = $this->createAdmin();

        $iri = '/api/admin/catalog/families/99999999';
        $query = <<<'GQL'
            query($id: ID!) {
              adminAttributeFamily(id: $id) {
                id _id code name
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);

        $response->assertOk();

        $hasErrors = ! empty($response->json('errors'));
        $dataNull = $response->json('data.adminAttributeFamily') === null;

        expect($hasErrors || $dataNull)->toBeTrue();
    }

    public function test_query_detail_requires_token(): void
    {
        $familyId = $this->insertFamily(['code' => 'gql-detail-auth-'.uniqid()]);
        $iri = '/api/admin/catalog/families/'.$familyId;

        $query = <<<'GQL'
            query($id: ID!) {
              adminAttributeFamily(id: $id) {
                id _id code name
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri]);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        expect(count($response->json('errors')))->toBeGreaterThan(0);
    }

    public function test_mutation_create_family_happy_path(): void
    {
        $admin = $this->createAdmin();
        $code = 'gql_fam_cr_'.uniqid();

        $mutation = <<<'GQL'
            mutation($input: createAdminAttributeFamilyInput!) {
              createAdminAttributeFamily(input: $input) {
                adminAttributeFamily { _id code name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['code' => $code, 'name' => 'GQL Created Family'],
        ], $admin);

        $response->assertOk();
        expect(\DB::table('attribute_families')->where('code', $code)->exists())->toBeTrue();
    }

    public function test_mutation_create_family_missing_code_returns_error(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation($input: createAdminAttributeFamilyInput!) {
              createAdminAttributeFamily(input: $input) {
                adminAttributeFamily { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['name' => 'No Code'],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_create_family_duplicate_code_returns_error(): void
    {
        $admin = $this->createAdmin();
        $code = 'gql_fam_dup_'.uniqid();
        $this->insertFamily(['code' => $code]);

        $mutation = <<<'GQL'
            mutation($input: createAdminAttributeFamilyInput!) {
              createAdminAttributeFamily(input: $input) {
                adminAttributeFamily { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['code' => $code, 'name' => 'Dup'],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_create_family_requires_auth(): void
    {
        $mutation = <<<'GQL'
            mutation($input: createAdminAttributeFamilyInput!) {
              createAdminAttributeFamily(input: $input) {
                adminAttributeFamily { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['code' => 'gql_fam_na_'.uniqid(), 'name' => 'NoAuth'],
        ]);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_update_family_happy_path(): void
    {
        $admin = $this->createAdmin();
        $familyId = $this->insertFamily(['code' => 'gql_fam_upd_'.uniqid(), 'name' => 'Before']);
        $code = \DB::table('attribute_families')->where('id', $familyId)->value('code');
        $iri = '/api/admin/catalog/families/'.$familyId;

        $mutation = <<<'GQL'
            mutation($input: updateAdminAttributeFamilyInput!) {
              updateAdminAttributeFamily(input: $input) {
                adminAttributeFamily { _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => $iri, 'code' => $code, 'name' => 'After GQL Update'],
        ], $admin);

        $response->assertOk();
        expect(\DB::table('attribute_families')->where('id', $familyId)->value('name'))->toBe('After GQL Update');
    }

    public function test_mutation_delete_family_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertFamily(['code' => 'gql_fam_keep_'.uniqid()]);
        $code = 'gql_fam_del_'.uniqid();
        $name = 'GQL Delete Family '.uniqid();
        $deleteId = $this->insertFamily(['code' => $code, 'name' => $name]);
        $iri = '/api/admin/catalog/families/'.$deleteId;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminAttributeFamilyInput!) {
              deleteAdminAttributeFamily(input: $input) {
                adminAttributeFamily {
                  id
                  _id
                  code
                  name
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => $iri],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $family = $response->json('data.deleteAdminAttributeFamily.adminAttributeFamily');
        expect($family['id'])->not()->toBeNull();
        expect((int) $family['_id'])->toBe($deleteId);
        expect($family['code'])->toBe($code);
        expect($family['name'])->toBe($name);

        expect(\DB::table('attribute_families')->where('id', $deleteId)->exists())->toBeFalse();
    }

    public function test_mutation_delete_family_with_products_returns_error(): void
    {
        $admin = $this->createAdmin();

        $defaultFamilyId = \DB::table('attribute_families')->where('code', 'default')->value('id')
            ?: $this->insertFamily(['code' => 'default']);

        if (\DB::table('products')->where('attribute_family_id', $defaultFamilyId)->count() === 0) {
            \DB::table('products')->insert([
                'type'                => 'simple',
                'attribute_family_id' => $defaultFamilyId,
                'sku'                 => 'gql_fam_prod_'.uniqid(),
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }

        $this->insertFamily(['code' => 'gql_fam_filler_'.uniqid()]);
        $iri = '/api/admin/catalog/families/'.$defaultFamilyId;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminAttributeFamilyInput!) {
              deleteAdminAttributeFamily(input: $input) {
                adminAttributeFamily { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        expect(\DB::table('attribute_families')->where('id', $defaultFamilyId)->exists())->toBeTrue();
    }

    public function test_mutation_delete_family_requires_auth(): void
    {
        $familyId = $this->insertFamily(['code' => 'gql_fam_del_na_'.uniqid()]);
        $iri = '/api/admin/catalog/families/'.$familyId;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminAttributeFamilyInput!) {
              deleteAdminAttributeFamily(input: $input) {
                adminAttributeFamily { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]]);
        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }
}

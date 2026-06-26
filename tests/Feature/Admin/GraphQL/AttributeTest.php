<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for the admin catalog attribute endpoints:
 *   - adminAttributes  (QueryCollection — listing with cursor pagination + filters)
 *   - adminAttribute   (Query — single attribute detail with translations and options)
 *
 * Seeds attributes via local helper methods copied from the REST AttributeTest.
 * Does NOT modify AdminApiTestCase.
 */
class AttributeTest extends AdminApiTestCase
{
    /**
     * Insert one attribute row and return the attribute ID.
     */
    protected function insertAttribute(array $overrides = []): int
    {
        return \DB::table('attributes')->insertGetId(array_merge([
            'code'                => 'gql_attr_'.uniqid(),
            'admin_name'          => 'GQL Test Attribute '.uniqid(),
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

    /**
     * Insert one attribute option row and return the option ID.
     */
    protected function insertAttributeOption(int $attributeId, array $overrides = []): int
    {
        return \DB::table('attribute_options')->insertGetId(array_merge([
            'attribute_id' => $attributeId,
            'admin_name'   => 'GQL Option '.uniqid(),
            'swatch_value' => null,
            'sort_order'   => 0,
        ], $overrides));
    }

    /**
     * Insert one attribute option translation row.
     */
    protected function insertAttributeOptionTranslation(int $optionId, string $locale, string $label): void
    {
        \DB::table('attribute_option_translations')->insert([
            'attribute_option_id' => $optionId,
            'locale'              => $locale,
            'label'               => $label,
        ]);
    }

    public function test_query_listing_returns_seeded_attribute(): void
    {
        $admin = $this->createAdmin();
        $code = 'gql-attr-unique-'.uniqid();
        $id = $this->insertAttribute([
            'code'       => $code,
            'admin_name' => 'GQL Listing Attribute',
            'type'       => 'text',
        ]);

        $query = <<<'GQL'
            query {
              adminAttributes(first: 100) {
                edges { node { id _id code type } }
                pageInfo { hasNextPage endCursor }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();

        $edges = $response->json('data.adminAttributes.edges');
        expect($edges)->toBeArray();
        expect(count($edges))->toBeGreaterThan(0);

        $edgeIds = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);
        expect($edgeIds)->toContain($id);

        $node = collect($edges)->first(fn ($e) => ($e['node']['_id'] ?? null) === $id);
        expect($node)->not()->toBeNull();
        expect($node['node']['code'])->toBe($code);
        expect($node['node']['type'])->toBe('text');
    }

    public function test_query_listing_filter_by_code_partial(): void
    {
        $admin = $this->createAdmin();
        $hitId = $this->insertAttribute(['code' => 'gql-attr-hit-'.uniqid()]);
        $missId = $this->insertAttribute(['code' => 'other-attr-'.uniqid()]);

        $query = <<<'GQL'
            query($code: String) {
              adminAttributes(first: 100, code: $code) {
                edges { node { _id code } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['code' => 'gql-attr-hit'], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.adminAttributes.edges');
        $edgeIds = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);

        expect($edgeIds)->toContain($hitId);
        expect($edgeIds)->not()->toContain($missId);
    }

    public function test_query_listing_filter_by_type(): void
    {
        $admin = $this->createAdmin();
        $selectId = $this->insertAttribute(['code' => 'gql-type-select-'.uniqid(), 'type' => 'select']);
        $textId = $this->insertAttribute(['code' => 'gql-type-text-'.uniqid(), 'type' => 'text']);

        $query = <<<'GQL'
            query($type: String) {
              adminAttributes(first: 100, type: $type) {
                edges { node { _id type } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['type' => 'select'], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.adminAttributes.edges');
        $edgeIds = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);

        expect($edgeIds)->toContain($selectId);
        expect($edgeIds)->not()->toContain($textId);

        foreach ($edges as $edge) {
            expect($edge['node']['type'])->toBe('select');
        }
    }

    public function test_query_listing_filter_by_is_user_defined(): void
    {
        $admin = $this->createAdmin();
        $userDefId = $this->insertAttribute(['code' => 'gql-user-def-'.uniqid(), 'is_user_defined' => 1]);
        $sysAttrId = $this->insertAttribute(['code' => 'gql-sys-attr-'.uniqid(), 'is_user_defined' => 0]);

        $query = <<<'GQL'
            query($is_user_defined: Int) {
              adminAttributes(first: 100, is_user_defined: $is_user_defined) {
                edges { node { _id isUserDefined } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['is_user_defined' => 1], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.adminAttributes.edges');
        $edgeIds = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);

        expect($edgeIds)->toContain($userDefId);
        expect($edgeIds)->not()->toContain($sysAttrId);
    }

    public function test_query_listing_requires_token(): void
    {
        $query = <<<'GQL'
            query {
              adminAttributes(first: 5) {
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

        $prefix = 'gql-page-'.uniqid().'-';
        for ($i = 1; $i <= 12; $i++) {
            $this->insertAttribute(['code' => $prefix.$i]);
        }

        $query = <<<'GQL'
            query($first: Int, $after: String) {
              adminAttributes(first: $first, after: $after) {
                edges { node { _id code } }
                pageInfo { hasNextPage endCursor }
              }
            }
        GQL;

        $firstResponse = $this->adminGraphQL($query, ['first' => 5], $admin);
        $firstResponse->assertOk();
        expect($firstResponse->json('errors'))->toBeNull();

        $firstEdges = $firstResponse->json('data.adminAttributes.edges');
        $endCursor = $firstResponse->json('data.adminAttributes.pageInfo.endCursor');
        $hasNextPage = $firstResponse->json('data.adminAttributes.pageInfo.hasNextPage');

        expect($firstEdges)->toBeArray();
        expect(count($firstEdges))->toBeGreaterThan(0);
        expect($endCursor)->not()->toBeNull();
        expect($hasNextPage)->toBeTrue();

        $secondResponse = $this->adminGraphQL($query, ['first' => 5, 'after' => $endCursor], $admin);
        $secondResponse->assertOk();
        expect($secondResponse->json('errors'))->toBeNull();

        $secondEdges = $secondResponse->json('data.adminAttributes.edges');
        expect($secondEdges)->toBeArray();
        expect(count($secondEdges))->toBeGreaterThan(0);

        $firstIds = array_map(fn ($e) => $e['node']['_id'] ?? null, $firstEdges);
        $secondIds = array_map(fn ($e) => $e['node']['_id'] ?? null, $secondEdges);
        expect(array_intersect($firstIds, $secondIds))->toBe([]);
    }

    public function test_query_detail_returns_attribute_with_translations_and_options(): void
    {
        $admin = $this->createAdmin();
        $attrId = $this->insertAttribute([
            'code'       => 'gql-select-detail-'.uniqid(),
            'admin_name' => 'GQL Select Detail',
            'type'       => 'select',
        ]);

        \DB::table('attribute_translations')->insert([
            ['attribute_id' => $attrId, 'locale' => 'en', 'name' => 'GQL Select EN'],
            ['attribute_id' => $attrId, 'locale' => 'fr', 'name' => 'GQL Sélection FR'],
        ]);

        $optId1 = $this->insertAttributeOption($attrId, ['admin_name' => 'Red', 'sort_order' => 1]);
        $optId2 = $this->insertAttributeOption($attrId, ['admin_name' => 'Blue', 'sort_order' => 2]);
        $this->insertAttributeOptionTranslation($optId1, 'en', 'Red');
        $this->insertAttributeOptionTranslation($optId2, 'en', 'Blue');

        $iri = '/api/admin/catalog/attributes/'.$attrId;
        $query = <<<'GQL'
            query($id: ID!) {
              adminAttribute(id: $id) {
                id _id code type translations options
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $attr = $response->json('data.adminAttribute');
        expect($attr)->not()->toBeNull();
        expect($attr['_id'])->toBe($attrId);

        $translations = $attr['translations'];
        expect($translations)->toBeArray();
        expect(count($translations))->toBeGreaterThanOrEqual(1);

        $options = $attr['options'];
        expect($options)->toBeArray();
        expect(count($options))->toBe(2);

        foreach ($options as $option) {
            expect($option)->toHaveKey('translations');
            expect($option['translations'])->toBeArray();
            expect(count($option['translations']))->toBeGreaterThanOrEqual(1);
        }
    }

    public function test_query_detail_unknown_id_returns_error(): void
    {
        $admin = $this->createAdmin();

        $iri = '/api/admin/catalog/attributes/99999999';
        $query = <<<'GQL'
            query($id: ID!) {
              adminAttribute(id: $id) {
                id _id code
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);

        $response->assertOk();

        $hasErrors = ! empty($response->json('errors'));
        $dataNull = $response->json('data.adminAttribute') === null;

        expect($hasErrors || $dataNull)->toBeTrue();
    }

    public function test_query_detail_requires_token(): void
    {
        $attrId = $this->insertAttribute(['code' => 'gql-detail-auth-'.uniqid()]);
        $iri = '/api/admin/catalog/attributes/'.$attrId;

        $query = <<<'GQL'
            query($id: ID!) {
              adminAttribute(id: $id) {
                id _id code
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri]);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        expect(count($response->json('errors')))->toBeGreaterThan(0);
    }

    public function test_mutation_create_attribute_happy_path(): void
    {
        $admin = $this->createAdmin();
        $code = 'gql_create_'.uniqid();

        $mutation = <<<'GQL'
            mutation($input: createAdminAttributeInput!) {
              createAdminAttribute(input: $input) {
                adminAttribute { id _id code type }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'code'      => $code,
                'adminName' => 'GQL Create Test',
                'type'      => 'text',
            ],
        ], $admin);

        $response->assertOk();

        $row = \DB::table('attributes')->where('code', $code)->first();
        expect($row)->not()->toBeNull();
        expect($row->type)->toBe('text');
    }

    public function test_mutation_create_attribute_missing_code_returns_error(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation($input: createAdminAttributeInput!) {
              createAdminAttribute(input: $input) {
                adminAttribute { _id code }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'adminName' => 'No Code',
                'type'      => 'text',
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_create_attribute_requires_auth(): void
    {
        $mutation = <<<'GQL'
            mutation($input: createAdminAttributeInput!) {
              createAdminAttribute(input: $input) {
                adminAttribute { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'code'      => 'gql_no_auth_'.uniqid(),
                'adminName' => 'No Auth',
                'type'      => 'text',
            ],
        ]);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_update_attribute_happy_path(): void
    {
        $admin = $this->createAdmin();
        $code = 'gql_upd_'.uniqid();
        $id = $this->insertAttribute(['code' => $code, 'admin_name' => 'Before Update', 'type' => 'text']);
        $iri = '/api/admin/catalog/attributes/'.$id;

        $mutation = <<<'GQL'
            mutation($input: updateAdminAttributeInput!) {
              updateAdminAttribute(input: $input) {
                adminAttribute { _id code }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'        => $iri,
                'code'      => $code,
                'adminName' => 'After Update',
                'type'      => 'text',
            ],
        ], $admin);

        $response->assertOk();

        $row = \DB::table('attributes')->where('id', $id)->first();
        expect($row->admin_name)->toBe('After Update');
    }

    public function test_mutation_update_attribute_code_change_returns_error(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute(['code' => 'gql_nochange_'.uniqid(), 'type' => 'text']);
        $iri = '/api/admin/catalog/attributes/'.$id;

        $mutation = <<<'GQL'
            mutation($input: updateAdminAttributeInput!) {
              updateAdminAttribute(input: $input) {
                adminAttribute { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'        => $iri,
                'code'      => 'different_code_gql',
                'adminName' => 'Test',
                'type'      => 'text',
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_delete_attribute_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute(['code' => 'gql_del_'.uniqid(), 'type' => 'text']);
        $iri = '/api/admin/catalog/attributes/'.$id;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminAttributeInput!) {
              deleteAdminAttribute(input: $input) {
                adminAttribute { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => $iri],
        ], $admin);

        $response->assertOk();

        expect(\DB::table('attributes')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_mutation_delete_system_attribute_returns_error(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertAttribute([
            'code'            => 'gql_sys_'.uniqid(),
            'is_user_defined' => 0,
        ]);
        $iri = '/api/admin/catalog/attributes/'.$id;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminAttributeInput!) {
              deleteAdminAttribute(input: $input) {
                adminAttribute { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => $iri],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        expect(\DB::table('attributes')->where('id', $id)->exists())->toBeTrue();
    }

    public function test_mutation_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertAttribute(['code' => 'gql_mass_a_'.uniqid()]);
        $id2 = $this->insertAttribute(['code' => 'gql_mass_b_'.uniqid()]);

        $mutation = <<<'GQL'
            mutation($input: createAdminAttributeMassDeleteInput!) {
              createAdminAttributeMassDelete(input: $input) {
                adminAttributeMassDelete { deleted message }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['indices' => [$id1, $id2]],
        ], $admin);

        $response->assertOk();

        expect(\DB::table('attributes')->where('id', $id1)->exists())->toBeFalse();
        expect(\DB::table('attributes')->where('id', $id2)->exists())->toBeFalse();
    }

    public function test_mutation_delete_attribute_snapshot_resolves(): void
    {
        $admin = $this->createAdmin();
        $code = 'gql_del_snap_'.uniqid();
        $id = $this->insertAttribute(['code' => $code, 'admin_name' => 'Snapshot Attr', 'type' => 'text']);
        $iri = '/api/admin/catalog/attributes/'.$id;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminAttributeInput!) {
              deleteAdminAttribute(input: $input) {
                adminAttribute { id _id code type adminName }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => $iri],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $attr = $response->json('data.deleteAdminAttribute.adminAttribute');
        expect($attr)->not()->toBeNull();
        expect($attr['_id'])->toBe($id);
        expect($attr['code'])->toBe($code);
        expect($attr['type'])->toBe('text');
        expect($attr['adminName'])->toBe('Snapshot Attr');

        expect(\DB::table('attributes')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_mutation_create_attribute_option_creates_and_resolves(): void
    {
        $admin = $this->createAdmin();
        $attrId = $this->insertAttribute([
            'code'       => 'gql_opt_create_'.uniqid(),
            'admin_name' => 'GQL Option Create',
            'type'       => 'select',
        ]);

        $mutation = <<<'GQL'
            mutation($input: createAdminAttributeOptionInput!) {
              createAdminAttributeOption(input: $input) {
                adminAttributeOption { _id attributeId adminName sortOrder swatchValue }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'attributeId'  => $attrId,
                'adminName'    => 'Wool',
                'sortOrder'    => 2,
                'translations' => ['en' => ['label' => 'Wool']],
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $option = $response->json('data.createAdminAttributeOption.adminAttributeOption');
        expect($option)->not()->toBeNull();
        expect($option['attributeId'])->toBe($attrId);
        expect($option['adminName'])->toBe('Wool');
        expect($option['sortOrder'])->toBe(2);

        $optionRow = \DB::table('attribute_options')
            ->where('attribute_id', $attrId)
            ->where('admin_name', 'Wool')
            ->first();
        expect($optionRow)->not()->toBeNull();
        expect((int) $optionRow->sort_order)->toBe(2);
    }

    public function test_mutation_update_attribute_option_updates_and_resolves(): void
    {
        $admin = $this->createAdmin();
        $attrId = $this->insertAttribute([
            'code'       => 'gql_opt_update_'.uniqid(),
            'admin_name' => 'GQL Option Update',
            'type'       => 'select',
        ]);
        $optId = $this->insertAttributeOption($attrId, ['admin_name' => 'Old Name', 'sort_order' => 1]);
        $iri = '/api/admin/catalog/attributes/'.$attrId.'/options/'.$optId;

        $mutation = <<<'GQL'
            mutation($input: updateAdminAttributeOptionInput!) {
              updateAdminAttributeOption(input: $input) {
                adminAttributeOption { _id attributeId adminName sortOrder swatchValue }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'          => $iri,
                'attributeId' => $attrId,
                'optionId'    => $optId,
                'adminName'   => 'New Name',
                'sortOrder'   => 5,
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $option = $response->json('data.updateAdminAttributeOption.adminAttributeOption');
        expect($option)->not()->toBeNull();
        expect($option['_id'])->toBe($optId);
        expect($option['attributeId'])->toBe($attrId);
        expect($option['adminName'])->toBe('New Name');
        expect($option['sortOrder'])->toBe(5);

        $optionRow = \DB::table('attribute_options')->where('id', $optId)->first();
        expect($optionRow)->not()->toBeNull();
        expect($optionRow->admin_name)->toBe('New Name');
        expect((int) $optionRow->sort_order)->toBe(5);
    }

    public function test_mutation_delete_attribute_option_snapshot_resolves(): void
    {
        $admin = $this->createAdmin();
        $attrId = $this->insertAttribute([
            'code'       => 'gql_opt_del_'.uniqid(),
            'admin_name' => 'GQL Option Delete',
            'type'       => 'select',
        ]);
        $optId = $this->insertAttributeOption($attrId, [
            'admin_name'   => 'Doomed Option',
            'sort_order'   => 3,
            'swatch_value' => '#ABCDEF',
        ]);
        $iri = '/api/admin/catalog/attributes/'.$attrId.'/options/'.$optId;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminAttributeOptionInput!) {
              deleteAdminAttributeOption(input: $input) {
                adminAttributeOption { _id attributeId adminName sortOrder swatchValue }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'          => $iri,
                'attributeId' => $attrId,
                'optionId'    => $optId,
            ],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $option = $response->json('data.deleteAdminAttributeOption.adminAttributeOption');
        expect($option)->not()->toBeNull();
        expect($option['_id'])->toBe($optId);
        expect($option['attributeId'])->toBe($attrId);
        expect($option['adminName'])->toBe('Doomed Option');
        expect($option['sortOrder'])->toBe(3);
        expect($option['swatchValue'])->toBe('#ABCDEF');

        expect(\DB::table('attribute_options')->where('id', $optId)->exists())->toBeFalse();
    }

    public function test_mutation_mass_delete_system_attribute_returns_error(): void
    {
        $admin = $this->createAdmin();
        $userId = $this->insertAttribute(['code' => 'gql_mss_user_'.uniqid()]);
        $sysId = $this->insertAttribute([
            'code'            => 'gql_mss_sys_'.uniqid(),
            'is_user_defined' => 0,
        ]);

        $mutation = <<<'GQL'
            mutation($input: createAdminAttributeMassDeleteInput!) {
              createAdminAttributeMassDelete(input: $input) {
                adminAttributeMassDelete { deleted }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['indices' => [$userId, $sysId]],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        expect(\DB::table('attributes')->where('id', $userId)->exists())->toBeTrue();
    }
}

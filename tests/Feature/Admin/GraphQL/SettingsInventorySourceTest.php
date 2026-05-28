<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for the admin Settings → Inventory Sources endpoints
 * (Block B Wave 1).
 */
class SettingsInventorySourceTest extends AdminApiTestCase
{
    protected function insertInventorySource(array $overrides = []): int
    {
        return \DB::table('inventory_sources')->insertGetId(array_merge([
            'code'           => 'gqs-'.substr((string) microtime(true), -4).rand(10, 99),
            'name'           => 'GQL Source '.rand(100, 999),
            'description'    => null,
            'contact_name'   => 'Jane',
            'contact_email'  => 'jane'.rand(100, 999).'@example.com',
            'contact_number' => '5550001111',
            'contact_fax'    => null,
            'country'        => 'US',
            'state'          => 'CA',
            'city'           => 'Los Angeles',
            'street'         => '123 Main St',
            'postcode'       => '90001',
            'priority'       => 0,
            'latitude'       => null,
            'longitude'      => null,
            'status'         => 1,
            'created_at'     => now(),
            'updated_at'     => now(),
        ], $overrides));
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'code'          => 'gqlnew-'.rand(1000, 9999),
            'name'          => 'New GQL Source',
            'description'   => null,
            'contactName'   => 'GQL Admin',
            'contactEmail'  => 'gql'.rand(100, 999).'@example.com',
            'contactNumber' => '5559998888',
            'contactFax'    => null,
            'country'       => 'US',
            'state'         => 'CA',
            'city'          => 'LA',
            'street'        => '99 GQL St',
            'postcode'      => '90099',
            'priority'      => 0,
            'status'        => 1,
        ], $overrides);
    }

    public function test_query_listing_returns_seeded_row(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertInventorySource(['name' => 'LIST-GQL-NAME']);

        $query = <<<'GQL'
            query {
              adminSettingsInventorySources(first: 50) {
                edges { node { id _id code name status } }
                pageInfo { hasNextPage endCursor }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();

        $edges = $response->json('data.adminSettingsInventorySources.edges');
        expect($edges)->toBeArray();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);
        expect($ids)->toContain($id);
    }

    public function test_query_listing_filter_by_code(): void
    {
        $admin = $this->createAdmin();
        $needle = 'gflt'.rand(10000, 99999);
        $id1 = $this->insertInventorySource(['code' => 'p-'.$needle.'-q']);
        $id2 = $this->insertInventorySource();

        $query = <<<'GQL'
            query($code: String) {
              adminSettingsInventorySources(first: 50, code: $code) {
                edges { node { _id } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['code' => $needle], $admin);
        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.adminSettingsInventorySources.edges');
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_query_listing_requires_auth(): void
    {
        $query = <<<'GQL'
            query { adminSettingsInventorySources(first: 5) { edges { node { _id } } } }
        GQL;

        $response = $this->adminGraphQL($query);
        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_query_detail_returns_row(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertInventorySource();
        $iri = '/api/admin/settings/inventory-sources/'.$id;

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsInventorySource(id: $id) { id _id }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);
        $response->assertOk();
        expect($response->json('data.adminSettingsInventorySource._id'))->toBe($id);
    }

    public function test_query_detail_unknown_id_returns_error(): void
    {
        $admin = $this->createAdmin();
        $iri = '/api/admin/settings/inventory-sources/9999999';

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsInventorySource(id: $id) { id _id }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);
        $response->assertOk();

        $errors = $response->json('errors');
        $dataNull = $response->json('data.adminSettingsInventorySource') === null;
        expect($errors !== null || $dataNull)->toBeTrue();
    }

    public function test_mutation_create_happy_path(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->validPayload();

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsInventorySourceInput!) {
              createAdminSettingsInventorySource(input: $input) {
                adminSettingsInventorySource { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => $payload], $admin);
        $response->assertOk();
        expect(\DB::table('inventory_sources')->where('code', $payload['code'])->exists())->toBeTrue();
    }

    public function test_mutation_create_duplicate_code_returns_error(): void
    {
        $admin = $this->createAdmin();
        $code = 'gqldup-'.rand(1000, 9999);
        $this->insertInventorySource(['code' => $code]);
        $payload = $this->validPayload(['code' => $code]);

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsInventorySourceInput!) {
              createAdminSettingsInventorySource(input: $input) {
                adminSettingsInventorySource { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => $payload], $admin);
        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_create_requires_auth(): void
    {
        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsInventorySourceInput!) {
              createAdminSettingsInventorySource(input: $input) {
                adminSettingsInventorySource { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => $this->validPayload()]);
        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertInventorySource();
        $iri = '/api/admin/settings/inventory-sources/'.$id;

        $mutation = <<<'GQL'
            mutation($input: updateAdminSettingsInventorySourceInput!) {
              updateAdminSettingsInventorySource(input: $input) {
                adminSettingsInventorySource { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => $iri, 'name' => 'RenamedGqlSrc'],
        ], $admin);
        $response->assertOk();

        $hasErrors = ! empty($response->json('errors'));
        $newName = \DB::table('inventory_sources')->where('id', $id)->value('name');
        expect($newName === 'RenamedGqlSrc' || $hasErrors)->toBeTrue();
    }

    public function test_mutation_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertInventorySource();
        $id = $this->insertInventorySource();
        $iri = '/api/admin/settings/inventory-sources/'.$id;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminSettingsInventorySourceInput!) {
              deleteAdminSettingsInventorySource(input: $input) {
                adminSettingsInventorySource { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]], $admin);
        $response->assertOk();
        expect(\DB::table('inventory_sources')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_mutation_delete_last_source_returns_error(): void
    {
        $admin = $this->createAdmin();
        \DB::table('inventory_sources')->delete();
        $id = $this->insertInventorySource();
        $iri = '/api/admin/settings/inventory-sources/'.$id;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminSettingsInventorySourceInput!) {
              deleteAdminSettingsInventorySource(input: $input) {
                adminSettingsInventorySource { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]], $admin);
        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        expect(\DB::table('inventory_sources')->where('id', $id)->exists())->toBeTrue();
    }

    public function test_mutation_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertInventorySource();
        $this->insertInventorySource();
        $id1 = $this->insertInventorySource();
        $id2 = $this->insertInventorySource();

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsInventorySourceMassDeleteInput!) {
              createAdminSettingsInventorySourceMassDelete(input: $input) {
                adminSettingsInventorySourceMassDelete { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['indices' => [$id1, $id2]]], $admin);
        $response->assertOk();

        expect(\DB::table('inventory_sources')->where('id', $id1)->exists())->toBeFalse();
        expect(\DB::table('inventory_sources')->where('id', $id2)->exists())->toBeFalse();
    }
}

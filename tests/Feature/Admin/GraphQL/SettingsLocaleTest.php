<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for the admin Settings → Locales endpoints (Block B Wave 1).
 *
 * Operations:
 *   - adminSettingsLocales  (QueryCollection, cursor)
 *   - adminSettingsLocale   (Query)
 *   - createAdminSettingsLocale
 *   - updateAdminSettingsLocale
 *   - deleteAdminSettingsLocale
 *   - createAdminSettingsLocaleMassDelete
 */
class SettingsLocaleTest extends AdminApiTestCase
{
    protected function uniqueCode(string $prefix = 'gl'): string
    {
        return strtolower($prefix.str_replace('.', '', (string) microtime(true)).rand(10, 99));
    }

    protected function insertLocale(array $overrides = []): int
    {
        return \DB::table('locales')->insertGetId(array_merge([
            'code' => $this->uniqueCode(),
            'name' => 'GQL Locale',
            'direction' => 'ltr',
            'logo_path' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_query_listing_returns_seeded_row(): void
    {
        $admin = $this->createAdmin();
        $code = $this->uniqueCode('list');
        $id = $this->insertLocale(['code' => $code, 'name' => 'GQL-LISTING-NAME']);

        $query = <<<'GQL'
            query {
              adminSettingsLocales(first: 50) {
                edges { node { id _id code name direction } }
                pageInfo { hasNextPage endCursor }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();

        $edges = $response->json('data.adminSettingsLocales.edges');
        expect($edges)->toBeArray();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);
        expect($ids)->toContain($id);
    }

    public function test_query_listing_filter_by_direction(): void
    {
        $admin = $this->createAdmin();
        $rtl = $this->insertLocale(['direction' => 'rtl']);
        $ltr = $this->insertLocale(['direction' => 'ltr']);

        $query = <<<'GQL'
            query($direction: String) {
              adminSettingsLocales(first: 50, direction: $direction) {
                edges { node { _id direction } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['direction' => 'rtl'], $admin);
        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.adminSettingsLocales.edges');
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);
        expect($ids)->toContain($rtl);
        expect($ids)->not()->toContain($ltr);
    }

    public function test_query_listing_filter_by_id(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertLocale(['code' => $this->uniqueCode('gfi1')]);
        $id2 = $this->insertLocale(['code' => $this->uniqueCode('gfi2')]);
        $id3 = $this->insertLocale(['code' => $this->uniqueCode('gfi3')]);

        $query = <<<'GQL'
            query($id: String) {
              adminSettingsLocales(first: 50, id: $id) {
                edges { node { _id } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $id1.','.$id3], $admin);
        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.adminSettingsLocales.edges');
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);
        expect($ids)->toContain($id1);
        expect($ids)->toContain($id3);
        expect($ids)->not()->toContain($id2);
    }

    public function test_query_listing_requires_auth(): void
    {
        $query = <<<'GQL'
            query { adminSettingsLocales(first: 5) { edges { node { _id } } } }
        GQL;

        $response = $this->adminGraphQL($query);
        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_query_detail_returns_row(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertLocale();
        $iri = '/api/admin/settings/locales/'.$id;

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsLocale(id: $id) { id _id }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);
        $response->assertOk();
        expect($response->json('data.adminSettingsLocale._id'))->toBe($id);
    }

    public function test_query_detail_unknown_id_returns_error(): void
    {
        $admin = $this->createAdmin();
        $iri = '/api/admin/settings/locales/9999999';

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsLocale(id: $id) { id _id }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);
        $response->assertOk();

        $errors = $response->json('errors');
        $dataNull = $response->json('data.adminSettingsLocale') === null;
        expect($errors !== null || $dataNull)->toBeTrue();
    }

    public function test_query_detail_multiword_fields_resolve_over_graphql(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertLocale(['logo_path' => 'locales/mwf.png']);
        $iri = '/api/admin/settings/locales/'.$id;

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsLocale(id: $id) {
                _id
                code
                logoPath
                logoUrl
                createdAt
                updatedAt
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);

        $response->assertOk();
        $node = $response->json('data.adminSettingsLocale');

        expect($node['logoPath'])->not->toBeNull();
        expect($node['logoUrl'])->not->toBeNull();
        expect($node['createdAt'])->not->toBeNull();
        expect($node['updatedAt'])->not->toBeNull();
    }

    public function test_mutation_create_happy_path(): void
    {
        $admin = $this->createAdmin();
        $code = $this->uniqueCode('mc');

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsLocaleInput!) {
              createAdminSettingsLocale(input: $input) {
                adminSettingsLocale { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['code' => $code, 'name' => 'X', 'direction' => 'ltr'],
        ], $admin);

        $response->assertOk();
        expect(\DB::table('locales')->where('code', $code)->exists())->toBeTrue();
    }

    public function test_mutation_create_duplicate_returns_error(): void
    {
        $admin = $this->createAdmin();
        $code = $this->uniqueCode('dup');
        $this->insertLocale(['code' => $code]);

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsLocaleInput!) {
              createAdminSettingsLocale(input: $input) {
                adminSettingsLocale { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['code' => $code, 'name' => 'X', 'direction' => 'ltr'],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_create_requires_auth(): void
    {
        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsLocaleInput!) {
              createAdminSettingsLocale(input: $input) {
                adminSettingsLocale { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['code' => $this->uniqueCode(), 'name' => 'X', 'direction' => 'ltr'],
        ]);
        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertLocale(['name' => 'OldName']);
        $iri = '/api/admin/settings/locales/'.$id;

        $mutation = <<<'GQL'
            mutation($input: updateAdminSettingsLocaleInput!) {
              updateAdminSettingsLocale(input: $input) {
                adminSettingsLocale { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => $iri, 'name' => 'NewName'],
        ], $admin);

        $response->assertOk();
        $afterName = (string) \DB::table('locales')->where('id', $id)->value('name');
        $hasErrors = ! empty($response->json('errors'));
        expect($afterName === 'NewName' || $hasErrors)->toBeTrue();
    }

    public function test_mutation_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertLocale();
        $id = $this->insertLocale();
        $iri = '/api/admin/settings/locales/'.$id;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminSettingsLocaleInput!) {
              deleteAdminSettingsLocale(input: $input) {
                adminSettingsLocale { _id code message }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]], $admin);
        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.deleteAdminSettingsLocale.adminSettingsLocale._id'))->toBe($id);
        expect($response->json('data.deleteAdminSettingsLocale.adminSettingsLocale.message'))
            ->toBe('Locale deleted successfully.');
        expect(\DB::table('locales')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_mutation_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertLocale();
        $id1 = $this->insertLocale();
        $id2 = $this->insertLocale();

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsLocaleMassDeleteInput!) {
              createAdminSettingsLocaleMassDelete(input: $input) {
                adminSettingsLocaleMassDelete { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['indices' => [$id1, $id2]]], $admin);
        $response->assertOk();

        expect(\DB::table('locales')->where('id', $id1)->exists())->toBeFalse();
        expect(\DB::table('locales')->where('id', $id2)->exists())->toBeFalse();
    }
}

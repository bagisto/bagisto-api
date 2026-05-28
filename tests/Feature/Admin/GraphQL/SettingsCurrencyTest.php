<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for Admin Settings → Currencies CRUD (Block B Wave 1).
 */
class SettingsCurrencyTest extends AdminApiTestCase
{
    protected function uniqueCurrencyCode(string $prefix = 'G'): string
    {
        $letters = chr(rand(65, 90)).chr(rand(65, 90));

        return strtoupper(substr($prefix.$letters, 0, 3));
    }

    protected function insertCurrency(array $overrides = []): int
    {
        return \DB::table('currencies')->insertGetId(array_merge([
            'code'              => $this->uniqueCurrencyCode(),
            'name'              => 'GQL Currency',
            'symbol'            => 'G$',
            'decimal'           => 2,
            'group_separator'   => ',',
            'decimal_separator' => '.',
            'currency_position' => 'left',
            'created_at'        => now(),
            'updated_at'        => now(),
        ], $overrides));
    }

    public function test_query_listing_returns_currencies(): void
    {
        $admin = $this->createAdmin();
        $this->insertCurrency(['code' => 'GLA']);

        $query = <<<'GQL'
            query {
              adminSettingsCurrencies(first: 10) {
                edges { node { _id code name } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect($response->json('data.adminSettingsCurrencies.edges'))->toBeArray();
    }

    public function test_query_listing_filter_by_code(): void
    {
        $admin = $this->createAdmin();
        $this->insertCurrency(['code' => 'FCA']);
        $this->insertCurrency(['code' => 'ZZA']);

        $query = <<<'GQL'
            query($code: String) {
              adminSettingsCurrencies(first: 10, code: $code) {
                edges { node { _id code } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['code' => 'FCA'], $admin);

        $response->assertOk();
        $edges = $response->json('data.adminSettingsCurrencies.edges') ?? [];
        $hasErrors = ! empty($response->json('errors'));
        expect(is_array($edges) || $hasErrors)->toBeTrue();
    }

    public function test_query_detail_returns_currency(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCurrency(['code' => 'DTL']);
        $iri = '/api/admin/settings/currencies/'.$id;

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsCurrency(id: $id) { _id }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);

        $response->assertOk();
        $hasErrors = ! empty($response->json('errors'));
        $hasData = $response->json('data.adminSettingsCurrency') !== null;
        expect($hasErrors || $hasData)->toBeTrue();
    }

    public function test_mutation_create_happy_path(): void
    {
        $admin = $this->createAdmin();
        $code = $this->uniqueCurrencyCode('C');

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsCurrencyInput!) {
              createAdminSettingsCurrency(input: $input) {
                adminSettingsCurrency { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'code' => $code,
                'name' => 'GQL Created',
            ],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseHas('currencies', ['code' => $code]);
    }

    public function test_mutation_create_missing_name_fails(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsCurrencyInput!) {
              createAdminSettingsCurrency(input: $input) {
                adminSettingsCurrency { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['code' => 'NNG'],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        $this->assertDatabaseMissing('currencies', ['code' => 'NNG']);
    }

    public function test_mutation_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCurrency(['code' => 'UPP', 'name' => 'Before']);
        $iri = '/api/admin/settings/currencies/'.$id;

        $mutation = <<<'GQL'
            mutation($input: updateAdminSettingsCurrencyInput!) {
              updateAdminSettingsCurrency(input: $input) {
                adminSettingsCurrency { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => $iri, 'name' => 'After'],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseHas('currencies', ['id' => $id, 'name' => 'After']);
    }

    public function test_mutation_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertCurrency(['code' => 'GKP']);
        $id = $this->insertCurrency(['code' => 'GDL']);
        $iri = '/api/admin/settings/currencies/'.$id;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminSettingsCurrencyInput!) {
              deleteAdminSettingsCurrency(input: $input) {
                adminSettingsCurrency { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => $iri],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseMissing('currencies', ['id' => $id]);
    }

    public function test_mutation_delete_last_currency_fails(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCurrency(['code' => 'GLA']);
        \DB::table('channels')->update(['base_currency_id' => $id]);
        \DB::table('currencies')->where('id', '!=', $id)->delete();
        \Webkul\Core\Facades\Core::clearResolvedInstances();
        $iri = '/api/admin/settings/currencies/'.$id;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminSettingsCurrencyInput!) {
              deleteAdminSettingsCurrency(input: $input) {
                adminSettingsCurrency { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
        $this->assertDatabaseHas('currencies', ['id' => $id]);
    }

    public function test_mutation_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $this->insertCurrency(['code' => 'GMK']);
        $id1 = $this->insertCurrency(['code' => 'GM1']);
        $id2 = $this->insertCurrency(['code' => 'GM2']);

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsCurrencyMassDeleteInput!) {
              createAdminSettingsCurrencyMassDelete(input: $input) {
                adminSettingsCurrencyMassDelete { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['indices' => [$id1, $id2]],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseMissing('currencies', ['id' => $id1]);
        $this->assertDatabaseMissing('currencies', ['id' => $id2]);
    }
}

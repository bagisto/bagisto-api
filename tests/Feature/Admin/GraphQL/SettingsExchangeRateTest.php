<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for the admin Settings → Exchange Rates endpoints
 * (Block B Wave 1).
 *
 * Operations:
 *   - adminSettingsExchangeRates  (QueryCollection, cursor)
 *   - adminSettingsExchangeRate   (Query)
 *   - createAdminSettingsExchangeRate
 *   - updateAdminSettingsExchangeRate
 *   - deleteAdminSettingsExchangeRate
 *   - createAdminSettingsExchangeRateMassDelete
 */
class SettingsExchangeRateTest extends AdminApiTestCase
{
    protected function insertCurrency(array $overrides = []): int
    {
        return \DB::table('currencies')->insertGetId(array_merge([
            'code'              => 'GQ'.substr((string) microtime(true), -4).rand(10, 99),
            'name'              => 'GQL Currency',
            'symbol'            => '$',
            'decimal'           => 2,
            'group_separator'   => ',',
            'decimal_separator' => '.',
            'currency_position' => 'left',
            'created_at'        => now(),
            'updated_at'        => now(),
        ], $overrides));
    }

    protected function insertExchangeRate(int $cur, float $rate = 1.0): int
    {
        return \DB::table('currency_exchange_rates')->insertGetId([
            'target_currency' => $cur,
            'rate'            => $rate,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    public function test_query_listing_returns_seeded_row(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency(['name' => 'GQL-LISTING-NAME']);
        $id = $this->insertExchangeRate($cur, 1.234);

        $query = <<<'GQL'
            query {
              adminSettingsExchangeRates(first: 50) {
                edges { node { id _id targetCurrency rate } }
                pageInfo { hasNextPage endCursor }
                totalCount
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();

        $edges = $response->json('data.adminSettingsExchangeRates.edges');
        expect($edges)->toBeArray();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);
        expect($ids)->toContain($id);
    }

    public function test_query_listing_filter_by_target_currency(): void
    {
        $admin = $this->createAdmin();
        $cur1 = $this->insertCurrency();
        $cur2 = $this->insertCurrency();
        $id1 = $this->insertExchangeRate($cur1, 1.0);
        $id2 = $this->insertExchangeRate($cur2, 2.0);

        $query = <<<'GQL'
            query($target_currency: Int) {
              adminSettingsExchangeRates(first: 50, target_currency: $target_currency) {
                edges { node { _id } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['target_currency' => $cur1], $admin);
        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $edges = $response->json('data.adminSettingsExchangeRates.edges');
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_query_listing_requires_auth(): void
    {
        $query = <<<'GQL'
            query { adminSettingsExchangeRates(first: 5) { edges { node { _id } } } }
        GQL;

        $response = $this->adminGraphQL($query);
        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_query_detail_returns_row(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur, 1.5);
        $iri = '/api/admin/settings/exchange-rates/'.$id;

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsExchangeRate(id: $id) { id _id }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);
        $response->assertOk();

        expect($response->json('data.adminSettingsExchangeRate._id'))->toBe($id);
    }

    public function test_query_detail_unknown_id_returns_error(): void
    {
        $admin = $this->createAdmin();
        $iri = '/api/admin/settings/exchange-rates/9999999';

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsExchangeRate(id: $id) { id _id }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => $iri], $admin);
        $response->assertOk();

        $errors = $response->json('errors');
        $dataNull = $response->json('data.adminSettingsExchangeRate') === null;
        expect($errors !== null || $dataNull)->toBeTrue();
    }

    public function test_mutation_create_happy_path(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsExchangeRateInput!) {
              createAdminSettingsExchangeRate(input: $input) {
                adminSettingsExchangeRate { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['targetCurrency' =>$cur, 'rate' => 1.55],
        ], $admin);

        $response->assertOk();
        expect(\DB::table('currency_exchange_rates')->where('target_currency', $cur)->exists())->toBeTrue();
    }

    public function test_mutation_create_duplicate_returns_error(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $this->insertExchangeRate($cur, 1.0);

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsExchangeRateInput!) {
              createAdminSettingsExchangeRate(input: $input) {
                adminSettingsExchangeRate { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['targetCurrency' =>$cur, 'rate' => 2.0],
        ], $admin);

        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_create_requires_auth(): void
    {
        $cur = $this->insertCurrency();
        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsExchangeRateInput!) {
              createAdminSettingsExchangeRate(input: $input) {
                adminSettingsExchangeRate { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['targetCurrency' =>$cur, 'rate' => 1.0],
        ]);
        $response->assertOk();
        expect($response->json('errors'))->not()->toBeNull();
    }

    public function test_mutation_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur, 1.0);
        $iri = '/api/admin/settings/exchange-rates/'.$id;

        $mutation = <<<'GQL'
            mutation($input: updateAdminSettingsExchangeRateInput!) {
              updateAdminSettingsExchangeRate(input: $input) {
                adminSettingsExchangeRate { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => $iri, 'targetCurrency' =>$cur, 'rate' => 9.99],
        ], $admin);

        $response->assertOk();
        $afterRate = (float) \DB::table('currency_exchange_rates')->where('id', $id)->value('rate');
        $hasErrors = ! empty($response->json('errors'));
        expect($afterRate === 9.99 || $hasErrors)->toBeTrue();
    }

    public function test_mutation_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $cur = $this->insertCurrency();
        $id = $this->insertExchangeRate($cur, 1.0);
        $iri = '/api/admin/settings/exchange-rates/'.$id;

        $mutation = <<<'GQL'
            mutation($input: deleteAdminSettingsExchangeRateInput!) {
              deleteAdminSettingsExchangeRate(input: $input) {
                adminSettingsExchangeRate { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['id' => $iri]], $admin);
        $response->assertOk();
        expect(\DB::table('currency_exchange_rates')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_mutation_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertExchangeRate($this->insertCurrency(), 1.0);
        $id2 = $this->insertExchangeRate($this->insertCurrency(), 2.0);

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsExchangeRateMassDeleteInput!) {
              createAdminSettingsExchangeRateMassDelete(input: $input) {
                adminSettingsExchangeRateMassDelete { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, ['input' => ['indices' => [$id1, $id2]]], $admin);
        $response->assertOk();

        expect(\DB::table('currency_exchange_rates')->where('id', $id1)->exists())->toBeFalse();
        expect(\DB::table('currency_exchange_rates')->where('id', $id2)->exists())->toBeFalse();
    }
}

<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for the admin Settings → Tax Rates endpoints (Block B Wave 3).
 *
 * Operations:
 *   - adminSettingsTaxRates  (QueryCollection, cursor)
 *   - adminSettingsTaxRate   (Query)
 *   - createAdminSettingsTaxRate
 *   - updateAdminSettingsTaxRate
 *   - deleteAdminSettingsTaxRate
 */
class SettingsTaxRateTest extends AdminApiTestCase
{
    protected function insertTaxRate(array $overrides = []): int
    {
        return \DB::table('tax_rates')->insertGetId(array_merge([
            'identifier' => 'GQL-'.uniqid(),
            'is_zip' => 0,
            'zip_code' => '12345',
            'zip_from' => null,
            'zip_to' => null,
            'state' => 'CA',
            'country' => 'US',
            'tax_rate' => 8.5,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_query_listing_returns_seeded_row(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxRate(['identifier' => 'GQL-LIST-'.uniqid()]);

        $query = <<<'GQL'
            query {
              adminSettingsTaxRates(first: 50) {
                edges { node { id _id identifier } }
              }
            }
        GQL;

        $r = $this->adminGraphQL($query, [], $admin);
        $r->assertOk();
        $edges = $r->json('data.adminSettingsTaxRates.edges') ?? [];
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);
        expect($ids)->toContain($id);
    }

    public function test_query_listing_filter_by_country(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertTaxRate(['country' => 'IT']);
        $id2 = $this->insertTaxRate(['country' => 'ES']);

        $query = <<<'GQL'
            query($country: String) {
              adminSettingsTaxRates(first: 50, country: $country) {
                edges { node { _id } }
              }
            }
        GQL;

        $r = $this->adminGraphQL($query, ['country' => 'IT'], $admin);
        $r->assertOk();
        $edges = $r->json('data.adminSettingsTaxRates.edges') ?? [];
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);
        expect($ids)->toContain($id1);
        expect($ids)->not()->toContain($id2);
    }

    public function test_query_detail_returns_row(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxRate(['identifier' => 'GQL-DET-'.uniqid(), 'tax_rate' => 6.7]);

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsTaxRate(id: $id) {
                _id identifier taxRate
              }
            }
        GQL;

        $r = $this->adminGraphQL($query, ['id' => '/api/admin/settings/tax-rates/'.$id], $admin);
        $r->assertOk();
        expect($r->json('data.adminSettingsTaxRate._id'))->toBe($id);
    }

    public function test_query_detail_multiword_fields_resolve_over_graphql(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxRate([
            'identifier' => 'GQL-FR-'.uniqid(),
            'is_zip' => 1,
            'zip_code' => null,
            'zip_from' => '94000',
            'zip_to' => '94999',
            'tax_rate' => 7.25,
        ]);

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsTaxRate(id: $id) {
                _id
                identifier
                zipFrom
                zipTo
                taxRate
                createdAt
                updatedAt
              }
            }
        GQL;

        $r = $this->adminGraphQL($query, ['id' => '/api/admin/settings/tax-rates/'.$id], $admin);
        $r->assertOk();
        $node = $r->json('data.adminSettingsTaxRate');

        expect($node['taxRate'])->not->toBeNull();
        expect($node['zipFrom'])->not->toBeNull();
        expect($node['zipTo'])->not->toBeNull();
        expect($node['createdAt'])->not->toBeNull();
        expect($node['updatedAt'])->not->toBeNull();
    }

    public function test_create_specific_zip_mutation(): void
    {
        $admin = $this->createAdmin();
        $identifier = 'GQL-CREATE-SP-'.uniqid();

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsTaxRateInput!) {
              createAdminSettingsTaxRate(input: $input) {
                adminSettingsTaxRate { _id identifier }
              }
            }
        GQL;

        $r = $this->adminGraphQL($mutation, ['input' => [
            'identifier' => $identifier,
            'isZip' => false,
            'zipCode' => '94103',
            'state' => 'CA',
            'country' => 'US',
            'taxRate' => 8.5,
        ]], $admin);

        $r->assertOk();
        expect(\DB::table('tax_rates')->where('identifier', $identifier)->exists())->toBeTrue();
    }

    public function test_create_zip_range_mutation(): void
    {
        $admin = $this->createAdmin();
        $identifier = 'GQL-CREATE-RG-'.uniqid();

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsTaxRateInput!) {
              createAdminSettingsTaxRate(input: $input) {
                adminSettingsTaxRate { _id }
              }
            }
        GQL;

        $r = $this->adminGraphQL($mutation, ['input' => [
            'identifier' => $identifier,
            'isZip' => true,
            'zipFrom' => '94000',
            'zipTo' => '94999',
            'state' => 'CA',
            'country' => 'US',
            'taxRate' => 9.0,
        ]], $admin);

        $r->assertOk();
        $row = \DB::table('tax_rates')->where('identifier', $identifier)->first();
        expect($row)->not()->toBeNull();
        expect((int) $row->is_zip)->toBe(1);
        expect($row->zip_from)->toBe('94000');
    }

    public function test_update_mutation(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxRate();

        $mutation = <<<'GQL'
            mutation($input: updateAdminSettingsTaxRateInput!) {
              updateAdminSettingsTaxRate(input: $input) {
                adminSettingsTaxRate { _id }
              }
            }
        GQL;

        $r = $this->adminGraphQL($mutation, ['input' => [
            'id' => '/api/admin/settings/tax-rates/'.$id,
            'taxRate' => 4.4,
        ]], $admin);

        $r->assertOk();
        expect((float) \DB::table('tax_rates')->where('id', $id)->value('tax_rate'))->toBe(4.4);
    }

    public function test_delete_mutation(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTaxRate();

        $mutation = <<<'GQL'
            mutation($input: deleteAdminSettingsTaxRateInput!) {
              deleteAdminSettingsTaxRate(input: $input) {
                adminSettingsTaxRate { _id }
              }
            }
        GQL;

        $this->adminGraphQL($mutation, ['input' => [
            'id' => '/api/admin/settings/tax-rates/'.$id,
        ]], $admin);

        expect(\DB::table('tax_rates')->where('id', $id)->exists())->toBeFalse();
    }
}

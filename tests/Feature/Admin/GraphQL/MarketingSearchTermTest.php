<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Marketing\Models\SearchTerm;

/**
 * GraphQL coverage for Admin Marketing → Search Terms (Block F3b).
 */
class MarketingSearchTermTest extends AdminApiTestCase
{
    protected function seedSearchTerm(array $overrides = []): SearchTerm
    {
        $this->seedRequiredData();

        return SearchTerm::factory()->create(array_merge([
            'term' => 'term-'.uniqid(),
            'uses' => 0,
        ], $overrides));
    }

    public function test_listing_resolves_display_flag_and_nullable_channel(): void
    {
        $admin = $this->createAdmin();
        $this->seedSearchTerm();

        $query = <<<'GQL'
            query {
              adminMarketingSearchTerms(first: 5) {
                edges {
                  node {
                    _id
                    term
                    displayInSuggestedTerms
                    channel { _id }
                  }
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
    }

    public function test_listing(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm();

        $query = <<<'GQL'
            query {
              adminMarketingSearchTerms(first: 50) {
                edges { node { id _id term uses } }
                totalCount
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, [], $admin);
        $resp->assertOk();
        expect($resp->json('errors'))->toBeNull();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $resp->json('data.adminMarketingSearchTerms.edges') ?? []);
        expect($ids)->toContain($s->id);
    }

    public function test_listing_filter_by_term(): void
    {
        $admin = $this->createAdmin();
        $unique = 'glob-'.uniqid();
        $hit = $this->seedSearchTerm(['term' => $unique]);
        $miss = $this->seedSearchTerm(['term' => 'other-'.uniqid()]);

        $query = <<<'GQL'
            query($v: String) {
              adminMarketingSearchTerms(first: 50, term: $v) {
                edges { node { _id } }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($query, ['v' => 'glob-'], $admin);
        $resp->assertOk();
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $resp->json('data.adminMarketingSearchTerms.edges') ?? []);
        expect($ids)->toContain($hit->id);
        expect($ids)->not()->toContain($miss->id);
    }

    public function test_listing_requires_auth(): void
    {
        $query = 'query { adminMarketingSearchTerms(first: 5) { edges { node { _id } } } }';
        $resp = $this->adminGraphQL($query);
        $resp->assertOk();
        expect($resp->json('errors'))->not()->toBeNull();
    }

    public function test_detail(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm();

        $query = <<<'GQL'
            query($id: ID!) { adminMarketingSearchTerm(id: $id) { id _id } }
        GQL;
        $resp = $this->adminGraphQL($query, ['id' => '/api/admin/marketing/search-terms/'.$s->id], $admin);
        $resp->assertOk();
        expect($resp->json('data.adminMarketingSearchTerm._id'))->toBe($s->id);
    }

    public function test_update_mutation(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm(['term' => 'before']);

        $mutation = <<<'GQL'
            mutation($input: updateAdminMarketingSearchTermInput!) {
              updateAdminMarketingSearchTerm(input: $input) {
                adminMarketingSearchTerm { id _id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => [
                'id'   => '/api/admin/marketing/search-terms/'.$s->id,
                'term' => 'after',
            ],
        ], $admin);
        $resp->assertOk();
        expect(SearchTerm::find($s->id)->term)->toBe('after');
    }

    public function test_delete_mutation(): void
    {
        $admin = $this->createAdmin();
        $s = $this->seedSearchTerm();

        $mutation = <<<'GQL'
            mutation($input: deleteAdminMarketingSearchTermInput!) {
              deleteAdminMarketingSearchTerm(input: $input) {
                adminMarketingSearchTerm { id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/marketing/search-terms/'.$s->id],
        ], $admin);
        $resp->assertOk();
        expect(SearchTerm::find($s->id))->toBeNull();
    }

    public function test_mass_delete_mutation(): void
    {
        $admin = $this->createAdmin();
        $a = $this->seedSearchTerm();
        $b = $this->seedSearchTerm();

        $mutation = <<<'GQL'
            mutation($input: createAdminMarketingSearchTermMassDeleteInput!) {
              createAdminMarketingSearchTermMassDelete(input: $input) {
                adminMarketingSearchTermMassDelete { id }
              }
            }
        GQL;
        $resp = $this->adminGraphQL($mutation, [
            'input' => ['indices' => [$a->id, $b->id]],
        ], $admin);
        $resp->assertOk();
        expect(SearchTerm::find($a->id))->toBeNull();
        expect(SearchTerm::find($b->id))->toBeNull();
    }
}

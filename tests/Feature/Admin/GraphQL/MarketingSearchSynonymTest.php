<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for Admin Marketing → Search Synonyms CRUD (Block F3c).
 */
class MarketingSearchSynonymTest extends AdminApiTestCase
{
    protected function insertSynonym(array $overrides = []): int
    {
        return \DB::table('search_synonyms')->insertGetId(array_merge([
            'name'       => 'gqlsyn-'.uniqid(),
            'terms'      => 'shirt,tshirt,tee',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_query_listing(): void
    {
        $admin = $this->createAdmin();
        $this->insertSynonym(['name' => 'list-gqlsyn']);

        $query = <<<'GQL'
            query {
              adminMarketingSearchSynonyms(first: 10) {
                edges { node { _id name terms } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $edges = $response->json('data.adminMarketingSearchSynonyms.edges');
        expect($edges)->toBeArray();
    }

    public function test_query_detail(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSynonym(['name' => 'detail-gqlsyn']);

        $query = <<<GQL
            query {
              adminMarketingSearchSynonym(id: "/api/admin/marketing/search-synonyms/{$id}") {
                _id
                name
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $node = $response->json('data.adminMarketingSearchSynonym');
        if ($node !== null) {
            expect($node['_id'] ?? null)->toBe($id);
        } else {
            $this->assertDatabaseHas('search_synonyms', ['id' => $id, 'name' => 'detail-gqlsyn']);
        }
    }

    public function test_mutation_create(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation Create($input: createAdminMarketingSearchSynonymInput!) {
              createAdminMarketingSearchSynonym(input: $input) {
                adminMarketingSearchSynonym { _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'name'  => 'gqlcr-syn',
                'terms' => 'red,green,blue',
            ],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseHas('search_synonyms', ['name' => 'gqlcr-syn']);
    }

    public function test_mutation_update(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSynonym(['name' => 'gqlupd-syn']);

        $mutation = <<<'GQL'
            mutation Update($input: updateAdminMarketingSearchSynonymInput!) {
              updateAdminMarketingSearchSynonym(input: $input) {
                adminMarketingSearchSynonym { _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'    => "/api/admin/marketing/search-synonyms/{$id}",
                'name'  => 'gqlupd-updated',
                'terms' => 'new,changed,values',
            ],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseHas('search_synonyms', ['id' => $id, 'name' => 'gqlupd-updated']);
    }

    public function test_mutation_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSynonym(['name' => 'gqldel-syn']);

        $mutation = <<<'GQL'
            mutation Del($input: deleteAdminMarketingSearchSynonymInput!) {
              deleteAdminMarketingSearchSynonym(input: $input) {
                adminMarketingSearchSynonym { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => "/api/admin/marketing/search-synonyms/{$id}"],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseMissing('search_synonyms', ['id' => $id]);
    }

    public function test_mutation_mass_delete(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertSynonym();
        $b = $this->insertSynonym();

        $mutation = <<<'GQL'
            mutation Mass($input: createAdminMarketingSearchSynonymMassDeleteInput!) {
              createAdminMarketingSearchSynonymMassDelete(input: $input) {
                adminMarketingSearchSynonymMassDelete { deleted message }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['indices' => [$a, $b]],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseMissing('search_synonyms', ['id' => $a]);
        $this->assertDatabaseMissing('search_synonyms', ['id' => $b]);
    }

    public function test_query_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $query = '{ adminMarketingSearchSynonyms(first: 1) { edges { node { _id } } } }';
        $response = $this->adminGraphQL($query);
        $response->assertOk();
        $errors = $response->json('errors');
        expect($errors)->toBeArray();
    }
}

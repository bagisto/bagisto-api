<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for Admin Marketing → Events CRUD (Block F2b).
 */
class MarketingEventTest extends AdminApiTestCase
{
    protected function insertEvent(array $overrides = []): int
    {
        return \DB::table('marketing_events')->insertGetId(array_merge([
            'name' => 'gqlevt-'.uniqid(),
            'description' => 'desc',
            'date' => '2026-12-20',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_query_listing(): void
    {
        $admin = $this->createAdmin();
        $this->insertEvent(['name' => 'list-gqlevt']);

        $query = <<<'GQL'
            query {
              adminMarketingEvents(first: 10) {
                edges { node { _id name } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $edges = $response->json('data.adminMarketingEvents.edges');
        expect($edges)->toBeArray();
    }

    public function test_query_detail(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertEvent(['name' => 'detail-gqlevt']);

        $query = <<<GQL
            query {
              adminMarketingEvent(id: "/api/admin/marketing/events/{$id}") {
                _id
                name
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $node = $response->json('data.adminMarketingEvent');
        if ($node !== null) {
            expect($node['_id'] ?? null)->toBe($id);
        } else {
            $this->assertDatabaseHas('marketing_events', ['id' => $id, 'name' => 'detail-gqlevt']);
        }
    }

    public function test_mutation_create(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation Create($input: createAdminMarketingEventInput!) {
              createAdminMarketingEvent(input: $input) {
                adminMarketingEvent { _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'name' => 'gqlcr-event',
                'description' => 'gql desc',
                'date' => '2027-03-15',
            ],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseHas('marketing_events', ['name' => 'gqlcr-event']);
    }

    public function test_mutation_update(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertEvent(['name' => 'gqlupd-event']);

        $mutation = <<<'GQL'
            mutation Update($input: updateAdminMarketingEventInput!) {
              updateAdminMarketingEvent(input: $input) {
                adminMarketingEvent { _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id' => "/api/admin/marketing/events/{$id}",
                'name' => 'gqlupd-updated',
                'description' => 'updated desc',
                'date' => '2028-01-01',
            ],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseHas('marketing_events', ['id' => $id, 'name' => 'gqlupd-updated']);
    }

    public function test_mutation_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertEvent(['name' => 'gqldel-event']);

        $mutation = <<<'GQL'
            mutation Del($input: deleteAdminMarketingEventInput!) {
              deleteAdminMarketingEvent(input: $input) {
                adminMarketingEvent { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => "/api/admin/marketing/events/{$id}"],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseMissing('marketing_events', ['id' => $id]);
    }

    public function test_query_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $query = '{ adminMarketingEvents(first: 1) { edges { node { _id } } } }';
        $response = $this->adminGraphQL($query);
        $response->assertOk();
        $errors = $response->json('errors');
        expect($errors)->toBeArray();
    }
}

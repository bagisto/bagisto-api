<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class MarketingEventTest extends AdminApiTestCase
{
    /**
     * Test query listing.
     */
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

    /**
     * Test query detail.
     */
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
            $this->assertDatabaseHas('marketing_events', [
                'id' => $id,
                'name' => 'detail-gqlevt',
            ]);
        }
    }

    /**
     * Test mutation create.
     */
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

    /**
     * Test mutation update.
     */
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
        $this->assertDatabaseHas('marketing_events', [
            'id' => $id,
            'name' => 'gqlupd-updated',
        ]);
    }

    /**
     * Test mutation delete happy path.
     */
    public function test_mutation_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertEvent(['name' => 'gqldel-event']);

        $mutation = <<<'GQL'
            mutation Del($input: deleteAdminMarketingEventInput!) {
              deleteAdminMarketingEvent(input: $input) {
                adminMarketingEvent { id _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => "/api/admin/marketing/events/{$id}"],
        ], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.deleteAdminMarketingEvent.adminMarketingEvent.id'))->toBe("/api/admin/marketing/events/{$id}");

        $this->assertDatabaseMissing('marketing_events', ['id' => $id]);
    }

    /**
     * Test query listing requires auth.
     */
    public function test_query_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $query = '{ adminMarketingEvents(first: 1) { edges { node { _id } } } }';
        $response = $this->adminGraphQL($query);
        $response->assertOk();
        $errors = $response->json('errors');
        expect($errors)->toBeArray();
    }

    /**
     * Insert event.
     */
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
}

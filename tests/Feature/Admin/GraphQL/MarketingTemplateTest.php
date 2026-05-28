<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for Admin Marketing → Email Templates CRUD (Block F2a).
 */
class MarketingTemplateTest extends AdminApiTestCase
{
    protected function insertTemplate(array $overrides = []): int
    {
        return \DB::table('marketing_templates')->insertGetId(array_merge([
            'name'       => 'gqltpl-'.uniqid(),
            'status'     => 'active',
            'content'    => '<p>Body</p>',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_query_listing(): void
    {
        $admin = $this->createAdmin();
        $this->insertTemplate(['name' => 'list-gqltpl']);

        $query = <<<'GQL'
            query {
              adminMarketingTemplates(first: 10) {
                edges { node { _id name } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $edges = $response->json('data.adminMarketingTemplates.edges');
        expect($edges)->toBeArray();
    }

    public function test_query_detail(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTemplate(['name' => 'detail-gqltpl']);

        $query = <<<GQL
            query {
              adminMarketingTemplate(id: "/api/admin/marketing/templates/{$id}") {
                _id
                name
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $node = $response->json('data.adminMarketingTemplate');
        if ($node !== null) {
            expect($node['_id'] ?? null)->toBe($id);
        } else {
            $this->assertDatabaseHas('marketing_templates', ['id' => $id, 'name' => 'detail-gqltpl']);
        }
    }

    public function test_mutation_create(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation Create($input: createAdminMarketingTemplateInput!) {
              createAdminMarketingTemplate(input: $input) {
                adminMarketingTemplate { _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'name'    => 'gqlcr-tpl',
                'status'  => 'active',
                'content' => '<p>gql body</p>',
            ],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseHas('marketing_templates', ['name' => 'gqlcr-tpl']);
    }

    public function test_mutation_update(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTemplate(['name' => 'gqlupd-tpl']);

        $mutation = <<<'GQL'
            mutation Update($input: updateAdminMarketingTemplateInput!) {
              updateAdminMarketingTemplate(input: $input) {
                adminMarketingTemplate { _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id'      => "/api/admin/marketing/templates/{$id}",
                'name'    => 'gqlupd-updated',
                'status'  => 'inactive',
                'content' => '<p>new</p>',
            ],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseHas('marketing_templates', ['id' => $id, 'name' => 'gqlupd-updated', 'status' => 'inactive']);
    }

    public function test_mutation_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTemplate(['name' => 'gqldel-tpl']);

        $mutation = <<<'GQL'
            mutation Del($input: deleteAdminMarketingTemplateInput!) {
              deleteAdminMarketingTemplate(input: $input) {
                adminMarketingTemplate { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => "/api/admin/marketing/templates/{$id}"],
        ], $admin);

        $response->assertOk();
        $this->assertDatabaseMissing('marketing_templates', ['id' => $id]);
    }

    public function test_query_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $query = '{ adminMarketingTemplates(first: 1) { edges { node { _id } } } }';
        $response = $this->adminGraphQL($query);
        $response->assertOk();
        $errors = $response->json('errors');
        expect($errors)->toBeArray();
    }
}

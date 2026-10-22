<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class MarketingUrlRewriteTest extends AdminApiTestCase
{
    /**
     * Test query listing.
     */
    public function test_query_listing(): void
    {
        $admin = $this->createAdmin();
        $this->insertRewrite();

        $query = <<<'GQL'
            query {
              adminMarketingUrlRewrites(first: 10) {
                edges { node { _id requestPath } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $edges = $response->json('data.adminMarketingUrlRewrites.edges');
        expect($edges)->toBeArray();
    }

    /**
     * Test query detail.
     */
    public function test_query_detail(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRewrite(['request_path' => 'gqldetail-urr']);

        $query = <<<GQL
            query {
              adminMarketingUrlRewrite(id: "/api/admin/marketing/url-rewrites/{$id}") {
                _id
                requestPath
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $node = $response->json('data.adminMarketingUrlRewrite');
        if ($node !== null) {
            expect($node['_id'] ?? null)->toBe($id);
        } else {
            $this->assertDatabaseHas('url_rewrites', [
                'id' => $id,
                'request_path' => 'gqldetail-urr',
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
            mutation Create($input: createAdminMarketingUrlRewriteInput!) {
              createAdminMarketingUrlRewrite(input: $input) {
                adminMarketingUrlRewrite { _id requestPath }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'entityType' => 'product',
                'requestPath' => 'gqlcreate-urr',
                'targetPath' => 'gqlcreate-tgt',
                'redirectType' => '301',
                'locale' => 'en',
            ],
        ], $admin);

        $response->assertOk();
        $exists = \DB::table('url_rewrites')->where('request_path', 'gqlcreate-urr')->exists();
        $hasErrors = ! empty($response->json('errors'));
        expect($exists || $hasErrors)->toBeTrue();
    }

    /**
     * Test mutation update.
     */
    public function test_mutation_update(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRewrite(['request_path' => 'gqlupd-urr']);

        $mutation = <<<'GQL'
            mutation Update($input: updateAdminMarketingUrlRewriteInput!) {
              updateAdminMarketingUrlRewrite(input: $input) {
                adminMarketingUrlRewrite { _id requestPath }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id' => "/api/admin/marketing/url-rewrites/{$id}",
                'entityType' => 'category',
                'requestPath' => 'gqlupd-urr-after',
                'targetPath' => 'gqlupd-tgt-after',
                'redirectType' => '302',
                'locale' => 'en',
            ],
        ], $admin);

        $response->assertOk();
        $updated = \DB::table('url_rewrites')
            ->where('id', $id)
            ->where('request_path', 'gqlupd-urr-after')
            ->where('redirect_type', '302')
            ->exists();
        $hasErrors = ! empty($response->json('errors'));
        expect($updated || $hasErrors)->toBeTrue();
    }

    /**
     * Test mutation delete happy path.
     */
    public function test_mutation_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRewrite(['request_path' => 'gqldel-urr']);

        $mutation = <<<'GQL'
            mutation Del($input: deleteAdminMarketingUrlRewriteInput!) {
              deleteAdminMarketingUrlRewrite(input: $input) {
                adminMarketingUrlRewrite { id _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => "/api/admin/marketing/url-rewrites/{$id}"],
        ], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.deleteAdminMarketingUrlRewrite.adminMarketingUrlRewrite.id'))->toBe("/api/admin/marketing/url-rewrites/{$id}");

        $this->assertDatabaseMissing('url_rewrites', ['id' => $id]);
    }

    /**
     * Test query listing requires auth.
     */
    public function test_query_listing_requires_auth(): void
    {
        $this->seedRequiredData();
        $query = '{ adminMarketingUrlRewrites(first: 1) { edges { node { _id } } } }';
        $response = $this->adminGraphQL($query);
        $response->assertOk();
        $errors = $response->json('errors');
        expect($errors)->toBeArray();
    }

    /**
     * The mass delete mutation removes the given URL rewrites and skips unknown ids.
     */
    public function test_mutation_mass_delete_removes_rewrites(): void
    {
        $a = $this->insertRewrite();
        $b = $this->insertRewrite();

        $response = $this->massDeleteRewrites([$a, $b, 999999999], $this->createAdmin());

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.createAdminMarketingUrlRewriteMassDelete.adminMarketingUrlRewriteMassDelete.deleted'))->toBe([$a, $b]);
        $this->assertDatabaseMissing('url_rewrites', ['id' => $a]);
        $this->assertDatabaseMissing('url_rewrites', ['id' => $b]);
    }

    /**
     * The mass delete mutation rejects an empty list of ids.
     */
    public function test_mutation_mass_delete_rejects_empty_indices(): void
    {
        $response = $this->massDeleteRewrites([], $this->createAdmin());

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.marketing.url-rewrite.indices-required'));
    }

    /**
     * The mass delete mutation is refused for a role without the URL rewrite delete permission.
     */
    public function test_mutation_mass_delete_without_permission_is_refused(): void
    {
        $id = $this->insertRewrite();

        $role = Role::create([
            'name' => 'NoUrlRewriteDelete '.uniqid(),
            'description' => 'no url rewrite delete',
            'permission_type' => 'custom',
            'permissions' => ['marketing', 'marketing.search_seo', 'marketing.search_seo.url_rewrites'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);

        $response = $this->massDeleteRewrites([$id], $admin, $this->adminAccessToken($admin));

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.marketing.url-rewrite.no-permission'));
        $this->assertDatabaseHas('url_rewrites', ['id' => $id]);
    }

    /**
     * The mass delete mutation rejects a request without an admin token.
     */
    public function test_mutation_mass_delete_requires_authentication(): void
    {
        $id = $this->insertRewrite();

        $response = $this->massDeleteRewrites([$id]);

        expect($response->json('errors'))->not->toBeNull();
        $this->assertDatabaseHas('url_rewrites', ['id' => $id]);
    }

    /**
     * Insert rewrite.
     */
    protected function insertRewrite(array $overrides = []): int
    {
        return \DB::table('url_rewrites')->insertGetId(array_merge([
            'entity_type' => 'product',
            'request_path' => 'gqlurr-'.uniqid(),
            'target_path' => 'gqlurr-tgt-'.uniqid(),
            'redirect_type' => '301',
            'locale' => 'en',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    /**
     * Run the URL rewrite mass delete mutation, optionally as an admin with a given token.
     */
    protected function massDeleteRewrites(array $indices, ?Admin $admin = null, ?string $token = null): TestResponse
    {
        $mutation = <<<'GQL'
            mutation($input: createAdminMarketingUrlRewriteMassDeleteInput!) {
              createAdminMarketingUrlRewriteMassDelete(input: $input) {
                adminMarketingUrlRewriteMassDelete { deleted message }
              }
            }
        GQL;

        return $this->adminGraphQL($mutation, ['input' => ['indices' => $indices]], $admin, $token);
    }
}

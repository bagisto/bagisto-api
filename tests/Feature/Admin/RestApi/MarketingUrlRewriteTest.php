<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

/**
 * REST coverage for Admin Marketing → URL Rewrites CRUD (Block F3a).
 */
class MarketingUrlRewriteTest extends AdminApiTestCase
{
    protected function insertRewrite(array $overrides = []): int
    {
        return \DB::table('url_rewrites')->insertGetId(array_merge([
            'entity_type' => 'product',
            'request_path' => 'old-path-'.uniqid(),
            'target_path' => 'new-path-'.uniqid(),
            'redirect_type' => '301',
            'locale' => 'en',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function adminPut(Admin $admin, string $url, array $data = [], ?string $token = null): TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete(Admin $admin, string $url, ?string $token = null): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    protected function createAdminWithoutPermissions(): Admin
    {
        $role = Role::create([
            'name' => 'Limited '.uniqid(),
            'description' => 'no url-rewrite perms',
            'permission_type' => 'custom',
            'permissions' => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    protected function basePayload(array $overrides = []): array
    {
        return array_merge([
            'entity_type' => 'product',
            'request_path' => 'api-old-'.uniqid(),
            'target_path' => 'api-new-'.uniqid(),
            'redirect_type' => '301',
            'locale' => 'en',
        ], $overrides);
    }

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/marketing/url-rewrites');
        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertRewrite();

        $response = $this->adminGet($admin, '/api/admin/marketing/url-rewrites');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertRewrite();

        $response = $this->adminGet($admin, '/api/admin/marketing/url-rewrites?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys(['id', 'entityType', 'requestPath', 'targetPath', 'redirectType', 'locale']);
    }

    public function test_filter_by_entity_type(): void
    {
        $admin = $this->createAdmin();
        $product = $this->insertRewrite(['entity_type' => 'product']);
        $category = $this->insertRewrite(['entity_type' => 'category']);

        $response = $this->adminGet($admin, '/api/admin/marketing/url-rewrites?entity_type=category');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($category);
        expect($ids)->not->toContain($product);
    }

    public function test_filter_by_locale(): void
    {
        $admin = $this->createAdmin();
        $en = $this->insertRewrite(['locale' => 'en']);
        $second = $this->insertRewrite(['locale' => 'en', 'entity_type' => 'cms_page']);

        $response = $this->adminGet($admin, '/api/admin/marketing/url-rewrites?locale=en');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($en);
        expect($ids)->toContain($second);
    }

    public function test_filter_by_redirect_type(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertRewrite(['redirect_type' => '301']);
        $b = $this->insertRewrite(['redirect_type' => '302']);

        $response = $this->adminGet($admin, '/api/admin/marketing/url-rewrites?redirect_type=302');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($b);
        expect($ids)->not->toContain($a);
    }

    public function test_filter_by_request_path_partial(): void
    {
        $admin = $this->createAdmin();
        $match = $this->insertRewrite(['request_path' => 'matchurl-XYZ']);
        $other = $this->insertRewrite(['request_path' => 'unrelated-abc']);

        $response = $this->adminGet($admin, '/api/admin/marketing/url-rewrites?request_path=matchurl');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($match);
        expect($ids)->not->toContain($other);
    }

    public function test_per_page_cap(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/marketing/url-rewrites?per_page=999');

        $response->assertOk();
        expect((int) $response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_detail_returns_payload(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRewrite([
            'entity_type' => 'cms_page',
            'request_path' => 'detail-request',
            'target_path' => 'detail-target',
        ]);

        $response = $this->adminGet($admin, '/api/admin/marketing/url-rewrites/'.$id);

        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('entityType'))->toBe('cms_page');
        expect($response->json('requestPath'))->toBe('detail-request');
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/marketing/url-rewrites/999999');
        $response->assertStatus(404);
    }

    public function test_detail_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertRewrite();
        $response = $this->publicGet('/api/admin/marketing/url-rewrites/'.$id);
        $response->assertStatus(401);
    }

    public function test_create_happy_path(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/marketing/url-rewrites', $this->basePayload([
            'entity_type' => 'category',
            'request_path' => 'created-req',
            'target_path' => 'created-tgt',
            'redirect_type' => '302',
        ]));

        $response->assertStatus(201);
        $id = $response->json('id');
        $this->assertDatabaseHas('url_rewrites', [
            'id' => $id,
            'entity_type' => 'category',
            'request_path' => 'created-req',
            'redirect_type' => '302',
        ]);
    }

    public function test_create_missing_entity_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['entity_type']);
        $response = $this->adminPost($admin, '/api/admin/marketing/url-rewrites', $payload);
        $response->assertStatus(422);
    }

    public function test_create_invalid_entity_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/marketing/url-rewrites', $this->basePayload([
            'entity_type' => 'bogus',
        ]));
        $response->assertStatus(422);
    }

    public function test_create_missing_request_path_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['request_path']);
        $response = $this->adminPost($admin, '/api/admin/marketing/url-rewrites', $payload);
        $response->assertStatus(422);
    }

    public function test_create_missing_target_path_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['target_path']);
        $response = $this->adminPost($admin, '/api/admin/marketing/url-rewrites', $payload);
        $response->assertStatus(422);
    }

    public function test_create_invalid_redirect_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/marketing/url-rewrites', $this->basePayload([
            'redirect_type' => '303',
        ]));
        $response->assertStatus(422);
    }

    public function test_create_unknown_locale_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/marketing/url-rewrites', $this->basePayload([
            'locale' => 'zz',
        ]));
        $response->assertStatus(422);
    }

    public function test_create_both_redirect_type_values_accepted(): void
    {
        $admin = $this->createAdmin();
        foreach (['301', '302'] as $rt) {
            $response = $this->adminPost($admin, '/api/admin/marketing/url-rewrites', $this->basePayload([
                'request_path' => 'rt-'.$rt.'-'.uniqid(),
                'redirect_type' => $rt,
            ]));
            $response->assertStatus(201);
        }
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->publicPost('/api/admin/marketing/url-rewrites', ['entity_type' => 'product']);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_create_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $response = $this->adminPost($admin, '/api/admin/marketing/url-rewrites', $this->basePayload());
        $response->assertStatus(403);
    }

    public function test_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRewrite(['request_path' => 'before-req']);

        $response = $this->adminPut($admin, '/api/admin/marketing/url-rewrites/'.$id, $this->basePayload([
            'entity_type' => 'category',
            'request_path' => 'after-req',
            'target_path' => 'after-tgt',
            'redirect_type' => '302',
        ]));

        $response->assertOk();
        expect($response->json('requestPath'))->toBe('after-req');
        $this->assertDatabaseHas('url_rewrites', [
            'id' => $id,
            'request_path' => 'after-req',
            'redirect_type' => '302',
        ]);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/marketing/url-rewrites/999999', $this->basePayload());
        $response->assertStatus(404);
    }

    public function test_update_invalid_redirect_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRewrite();
        $response = $this->adminPut($admin, '/api/admin/marketing/url-rewrites/'.$id, $this->basePayload([
            'redirect_type' => '404',
        ]));
        $response->assertStatus(422);
    }

    public function test_update_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertRewrite();
        $response = $this->putJson('/api/admin/marketing/url-rewrites/'.$id, ['entity_type' => 'product']);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_update_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertRewrite();
        $response = $this->adminPut($admin, '/api/admin/marketing/url-rewrites/'.$id, $this->basePayload());
        $response->assertStatus(403);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertRewrite();

        $response = $this->adminDelete($admin, '/api/admin/marketing/url-rewrites/'.$id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('url_rewrites', ['id' => $id]);
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/marketing/url-rewrites/999999');
        $response->assertStatus(404);
    }

    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertRewrite();
        $response = $this->deleteJson('/api/admin/marketing/url-rewrites/'.$id);
        expect(in_array($response->getStatusCode(), [401, 403]))->toBeTrue();
    }

    public function test_delete_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertRewrite();
        $response = $this->adminDelete($admin, '/api/admin/marketing/url-rewrites/'.$id);
        $response->assertStatus(403);
    }

    public function test_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertRewrite();
        $b = $this->insertRewrite();

        $response = $this->adminPost($admin, '/api/admin/marketing/url-rewrites/mass-delete', [
            'indices' => [$a, $b],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('url_rewrites', ['id' => $a]);
        $this->assertDatabaseMissing('url_rewrites', ['id' => $b]);
    }

    public function test_mass_delete_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/marketing/url-rewrites/mass-delete', ['indices' => []]);
        $response->assertStatus(422);
    }

    public function test_mass_delete_skips_unknown_ids(): void
    {
        $admin = $this->createAdmin();
        $real = $this->insertRewrite();

        $response = $this->adminPost($admin, '/api/admin/marketing/url-rewrites/mass-delete', [
            'indices' => [$real, 999999],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('url_rewrites', ['id' => $real]);
        expect($response->json('deleted'))->toBe([$real]);
    }

    public function test_mass_delete_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertRewrite();
        $response = $this->adminPost($admin, '/api/admin/marketing/url-rewrites/mass-delete', ['indices' => [$id]]);
        $response->assertStatus(403);
    }
}

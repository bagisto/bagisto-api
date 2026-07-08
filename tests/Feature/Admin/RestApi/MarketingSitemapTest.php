<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Core\Models\CoreConfig;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

/**
 * REST coverage for Admin Marketing → Sitemaps CRUD + generate (Block F3d).
 */
class MarketingSitemapTest extends AdminApiTestCase
{
    protected function insertSitemap(array $overrides = []): int
    {
        return DB::table('sitemaps')->insertGetId(array_merge([
            'file_name' => 'sitemap-'.uniqid().'.xml',
            'path' => '/',
            'generated_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function adminPut(Admin $admin, string $url, array $data = []): TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin));
    }

    protected function adminDelete(Admin $admin, string $url): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin));
    }

    protected function createAdminWithoutPermissions(): Admin
    {
        $role = Role::create([
            'name' => 'Limited '.uniqid(),
            'description' => 'no sitemap perms',
            'permission_type' => 'custom',
            'permissions' => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    protected function basePayload(array $overrides = []): array
    {
        return array_merge([
            'file_name' => 'api-sitemap-'.uniqid().'.xml',
            'path' => '/',
        ], $overrides);
    }

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $this->publicGet('/api/admin/marketing/sitemaps')->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertSitemap();

        $response = $this->adminGet($admin, '/api/admin/marketing/sitemaps');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertSitemap();

        $response = $this->adminGet($admin, '/api/admin/marketing/sitemaps?per_page=1');
        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys(['id', 'fileName', 'path', 'generatedAt']);
    }

    public function test_filter_by_file_name(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSitemap(['file_name' => 'unique-needle.xml']);
        $this->insertSitemap(['file_name' => 'other.xml']);

        $response = $this->adminGet($admin, '/api/admin/marketing/sitemaps?file_name=unique-needle');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_sort_by_file_name_asc(): void
    {
        $admin = $this->createAdmin();
        $this->insertSitemap(['file_name' => 'zzz-srt-sitemap.xml']);
        $this->insertSitemap(['file_name' => 'aaa-srt-sitemap.xml']);

        $response = $this->adminGet($admin, '/api/admin/marketing/sitemaps?sort=file_name&order=asc');
        $response->assertOk();
        $names = collect($response->json('data'))->pluck('fileName')->all();
        $idxA = array_search('aaa-srt-sitemap.xml', $names, true);
        $idxZ = array_search('zzz-srt-sitemap.xml', $names, true);
        if ($idxA !== false && $idxZ !== false) {
            expect($idxA)->toBeLessThan($idxZ);
        }
    }

    public function test_per_page_cap(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/marketing/sitemaps?per_page=999');
        $response->assertOk();
        expect((int) $response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_detail_returns_payload(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSitemap(['file_name' => 'detail-sm.xml', 'path' => '/']);

        $response = $this->adminGet($admin, '/api/admin/marketing/sitemaps/'.$id);
        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('fileName'))->toBe('detail-sm.xml');
        expect($response->json('path'))->toBe('/');
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminGet($admin, '/api/admin/marketing/sitemaps/999999')->assertStatus(404);
    }

    public function test_detail_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertSitemap();
        $this->publicGet('/api/admin/marketing/sitemaps/'.$id)->assertStatus(401);
    }

    public function test_create_happy_path(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload(['file_name' => 'created-sm.xml']);

        $response = $this->adminPost($admin, '/api/admin/marketing/sitemaps', $payload);
        $response->assertStatus(201);
        $id = $response->json('id');
        $this->assertDatabaseHas('sitemaps', ['id' => $id, 'file_name' => 'created-sm.xml']);
    }

    public function test_create_missing_file_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['file_name']);
        $this->adminPost($admin, '/api/admin/marketing/sitemaps', $payload)->assertStatus(422);
    }

    public function test_create_invalid_file_name_extension_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/marketing/sitemaps', $this->basePayload([
            'file_name' => 'no-extension',
        ]))->assertStatus(422);
    }

    public function test_create_missing_path_returns_422(): void
    {
        $admin = $this->createAdmin();
        $payload = $this->basePayload();
        unset($payload['path']);
        $this->adminPost($admin, '/api/admin/marketing/sitemaps', $payload)->assertStatus(422);
    }

    public function test_create_invalid_path_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/marketing/sitemaps', $this->basePayload([
            'path' => '/no-trailing',
        ]))->assertStatus(422);
    }

    public function test_create_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $this->publicPost('/api/admin/marketing/sitemaps', $this->basePayload())->assertStatus(401);
    }

    public function test_create_requires_permission(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $this->adminPost($admin, '/api/admin/marketing/sitemaps', $this->basePayload())->assertStatus(403);
    }

    public function test_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSitemap(['file_name' => 'old.xml']);

        $payload = $this->basePayload(['file_name' => 'new.xml']);

        $response = $this->adminPut($admin, '/api/admin/marketing/sitemaps/'.$id, $payload);
        $response->assertOk();
        $this->assertDatabaseHas('sitemaps', ['id' => $id, 'file_name' => 'new.xml']);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminPut($admin, '/api/admin/marketing/sitemaps/999999', $this->basePayload())->assertStatus(404);
    }

    public function test_update_invalid_file_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSitemap();
        $this->adminPut($admin, '/api/admin/marketing/sitemaps/'.$id, $this->basePayload([
            'file_name' => 'no-ext',
        ]))->assertStatus(422);
    }

    public function test_update_requires_permission(): void
    {
        $id = $this->insertSitemap();
        $admin = $this->createAdminWithoutPermissions();
        $this->adminPut($admin, '/api/admin/marketing/sitemaps/'.$id, $this->basePayload())->assertStatus(403);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertSitemap();

        $response = $this->adminDelete($admin, '/api/admin/marketing/sitemaps/'.$id);
        $response->assertOk();
        $this->assertDatabaseMissing('sitemaps', ['id' => $id]);
    }

    public function test_delete_removes_storage_files(): void
    {
        Storage::fake('local');
        $admin = $this->createAdmin();

        $filePath = 'fake-sitemap-'.uniqid().'.xml';
        Storage::put($filePath, '<urlset/>');
        $indexPath = 'fake-index-'.uniqid().'.xml';
        Storage::put($indexPath, '<sitemapindex/>');

        $id = $this->insertSitemap();
        DB::table('sitemaps')->where('id', $id)->update([
            'additional' => json_encode(['index' => $indexPath, 'sitemaps' => [$filePath]]),
        ]);

        Storage::assertExists($filePath);
        Storage::assertExists($indexPath);

        $response = $this->adminDelete($admin, '/api/admin/marketing/sitemaps/'.$id);
        $response->assertOk();

        $this->assertDatabaseMissing('sitemaps', ['id' => $id]);
        Storage::assertMissing($filePath);
        Storage::assertMissing($indexPath);
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminDelete($admin, '/api/admin/marketing/sitemaps/999999')->assertStatus(404);
    }

    public function test_delete_requires_permission(): void
    {
        $id = $this->insertSitemap();
        $admin = $this->createAdminWithoutPermissions();
        $this->adminDelete($admin, '/api/admin/marketing/sitemaps/'.$id)->assertStatus(403);
    }

    public function test_generate_happy_path(): void
    {
        Storage::fake('public');
        CoreConfig::query()->updateOrCreate(
            ['code' => 'general.sitemap.settings.enabled', 'channel_code' => null, 'locale_code' => null],
            ['value' => '1']
        );
        CoreConfig::query()->updateOrCreate(
            ['code' => 'general.sitemap.file_limits.max_url_per_file', 'channel_code' => null, 'locale_code' => null],
            ['value' => '50000']
        );

        $admin = $this->createAdmin();
        $id = $this->insertSitemap(['file_name' => 'gen-sm.xml', 'path' => '/']);

        $response = $this->adminPost($admin, '/api/admin/marketing/sitemaps/'.$id.'/generate');
        $response->assertOk();
        expect($response->json('sitemapId'))->toBe($id);

        $row = DB::table('sitemaps')->where('id', $id)->first();
        expect($row->generated_at)->not->toBeNull();
    }

    public function test_generate_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $this->adminPost($admin, '/api/admin/marketing/sitemaps/999999/generate')->assertStatus(404);
    }

    public function test_generate_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $id = $this->insertSitemap();
        $this->publicPost('/api/admin/marketing/sitemaps/'.$id.'/generate')->assertStatus(401);
    }

    public function test_generate_requires_permission(): void
    {
        $id = $this->insertSitemap();
        $admin = $this->createAdminWithoutPermissions();
        $this->adminPost($admin, '/api/admin/marketing/sitemaps/'.$id.'/generate')->assertStatus(403);
    }
}

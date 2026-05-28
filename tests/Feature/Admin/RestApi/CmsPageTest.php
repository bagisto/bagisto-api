<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * REST coverage for the admin CMS Pages endpoints (CMS Phase 1 + 2).
 */
class CmsPageTest extends AdminApiTestCase
{
    // -------------------------------------------------------------------------
    // Seed helpers
    // -------------------------------------------------------------------------

    protected function insertCmsPage(array $translation = [], array $channels = []): int
    {
        $pageId = \DB::table('cms_pages')->insertGetId([
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('cms_page_translations')->insert(array_merge([
            'cms_page_id'      => $pageId,
            'locale'           => 'en',
            'page_title'       => 'Test Page '.$pageId,
            'url_key'          => 'test-page-'.$pageId,
            'html_content'     => '<p>content</p>',
            'meta_title'       => null,
            'meta_keywords'    => null,
            'meta_description' => null,
        ], $translation));

        if (! empty($channels)) {
            foreach ($channels as $cid) {
                \DB::table('cms_page_channels')->insert([
                    'cms_page_id' => $pageId,
                    'channel_id'  => $cid,
                ]);
            }
        } else {
            $defaultChannel = \DB::table('channels')->value('id') ?: 1;
            \DB::table('cms_page_channels')->insert([
                'cms_page_id' => $pageId,
                'channel_id'  => $defaultChannel,
            ]);
        }

        return $pageId;
    }

    protected function adminPut(\Webkul\User\Models\Admin $admin, string $url, array $data = [], ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete(\Webkul\User\Models\Admin $admin, string $url, ?string $token = null): \Illuminate\Testing\TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    protected function defaultChannelId(): int
    {
        return (int) (\DB::table('channels')->value('id') ?: 1);
    }

    protected function createAdminWithoutPermissions(): \Webkul\User\Models\Admin
    {
        $role = \Webkul\User\Models\Role::create([
            'name'            => 'Limited '.uniqid(),
            'description'     => 'no cms perms',
            'permission_type' => 'custom',
            'permissions'     => ['catalog.products'],
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    // -------------------------------------------------------------------------
    // Listing
    // -------------------------------------------------------------------------

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/cms/pages');
        $response->assertStatus(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $this->insertCmsPage(['page_title' => 'Hello', 'url_key' => 'hello-'.uniqid()]);

        $response = $this->adminGet($admin, '/api/admin/cms/pages');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
    }

    public function test_listing_row_shape(): void
    {
        $admin = $this->createAdmin();
        $this->insertCmsPage();

        $response = $this->adminGet($admin, '/api/admin/cms/pages?per_page=1');

        $response->assertOk();
        $row = $response->json('data.0');
        expect($row)->toHaveKeys(['id', 'urlKey', 'pageTitle', 'channel', 'locale', 'createdAt']);
    }

    public function test_filter_by_url_key(): void
    {
        $admin = $this->createAdmin();
        $slug = 'flt-url-'.uniqid();
        $id = $this->insertCmsPage(['url_key' => $slug]);
        $this->insertCmsPage(['url_key' => 'other-'.uniqid()]);

        $response = $this->adminGet($admin, '/api/admin/cms/pages?url_key='.$slug);

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_filter_by_page_title(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCmsPage(['page_title' => 'UniqueTitleAlpha', 'url_key' => 'ut-a-'.uniqid()]);
        $this->insertCmsPage(['page_title' => 'OtherTitle', 'url_key' => 'ot-'.uniqid()]);

        $response = $this->adminGet($admin, '/api/admin/cms/pages?page_title=UniqueTitleAlpha');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_filter_by_channel(): void
    {
        $admin = $this->createAdmin();
        $cid = $this->defaultChannelId();
        $id = $this->insertCmsPage(['url_key' => 'fcc-'.uniqid()], [$cid]);

        $response = $this->adminGet($admin, '/api/admin/cms/pages?channel='.$cid);

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($id);
    }

    public function test_sort_by_id_desc_default(): void
    {
        $admin = $this->createAdmin();
        $this->insertCmsPage();
        $latest = $this->insertCmsPage();

        $response = $this->adminGet($admin, '/api/admin/cms/pages');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids[0])->toBeGreaterThanOrEqual($latest);
    }

    public function test_sort_by_page_title_asc(): void
    {
        $admin = $this->createAdmin();
        $this->insertCmsPage(['page_title' => 'Zeta', 'url_key' => 'z-'.uniqid()]);
        $this->insertCmsPage(['page_title' => 'Alpha', 'url_key' => 'a-'.uniqid()]);

        $response = $this->adminGet($admin, '/api/admin/cms/pages?sort=page_title-asc');
        $response->assertOk();
        $titles = collect($response->json('data'))->pluck('pageTitle')->filter()->values()->all();
        $sorted = $titles;
        sort($sorted);
        expect($titles)->toBe($sorted);
    }

    public function test_per_page_cap(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/cms/pages?per_page=9999');
        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    // -------------------------------------------------------------------------
    // Detail
    // -------------------------------------------------------------------------

    public function test_detail_requires_admin_token(): void
    {
        $id = $this->insertCmsPage();
        $response = $this->publicGet('/api/admin/cms/pages/'.$id);
        $response->assertStatus(401);
    }

    public function test_detail_returns_full_payload(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCmsPage(['page_title' => 'Detail', 'url_key' => 'dtl-'.uniqid(), 'html_content' => '<h1>X</h1>']);

        $response = $this->adminGet($admin, '/api/admin/cms/pages/'.$id);

        $response->assertOk();
        $body = $response->json();
        expect($body)->toHaveKeys(['id', 'urlKey', 'pageTitle', 'htmlContent', 'translations', 'channels']);
        expect($body['id'])->toBe($id);
        expect($body['translations'])->toBeArray();
        expect(count($body['translations']))->toBeGreaterThanOrEqual(1);
        expect($body['channels'])->toBeArray();
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/cms/pages/9999999');
        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Create
    // -------------------------------------------------------------------------

    public function test_create_happy_path(): void
    {
        $admin = $this->createAdmin();
        $slug = 'cr-'.uniqid();

        $response = $this->adminPost($admin, '/api/admin/cms/pages', [
            'url_key'      => $slug,
            'page_title'   => 'Created',
            'html_content' => '<p>hi</p>',
            'channels'     => [$this->defaultChannelId()],
        ]);

        $response->assertStatus(201);
        expect($response->json('id'))->toBeInt();
        expect(\DB::table('cms_page_translations')->where('url_key', $slug)->exists())->toBeTrue();
    }

    public function test_create_missing_url_key_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/cms/pages', [
            'page_title'   => 'NoUrl',
            'html_content' => '<p>x</p>',
            'channels'     => [$this->defaultChannelId()],
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_missing_page_title_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/cms/pages', [
            'url_key'      => 'np-'.uniqid(),
            'html_content' => '<p>x</p>',
            'channels'     => [$this->defaultChannelId()],
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_missing_html_content_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/cms/pages', [
            'url_key'    => 'nh-'.uniqid(),
            'page_title' => 'NoHtml',
            'channels'   => [$this->defaultChannelId()],
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_missing_channels_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/cms/pages', [
            'url_key'      => 'nc-'.uniqid(),
            'page_title'   => 'NoCh',
            'html_content' => '<p>x</p>',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_duplicate_url_key_returns_422(): void
    {
        $admin = $this->createAdmin();
        $slug = 'dup-'.uniqid();
        $this->insertCmsPage(['url_key' => $slug]);

        $response = $this->adminPost($admin, '/api/admin/cms/pages', [
            'url_key'      => $slug,
            'page_title'   => 'Dup',
            'html_content' => '<p>x</p>',
            'channels'     => [$this->defaultChannelId()],
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_invalid_url_key_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/cms/pages', [
            'url_key'      => 'Invalid URL KEY!',
            'page_title'   => 'Bad',
            'html_content' => '<p>x</p>',
            'channels'     => [$this->defaultChannelId()],
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_unknown_channel_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/cms/pages', [
            'url_key'      => 'uc-'.uniqid(),
            'page_title'   => 'BadCh',
            'html_content' => '<p>x</p>',
            'channels'     => [999999],
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/cms/pages', [
            'url_key'      => 'na-'.uniqid(),
            'page_title'   => 'X',
            'html_content' => '<p>x</p>',
            'channels'     => [1],
        ]);
        expect($response->getStatusCode())->toBe(401);
    }

    public function test_create_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $response = $this->adminPost($admin, '/api/admin/cms/pages', [
            'url_key'      => 'np-'.uniqid(),
            'page_title'   => 'X',
            'html_content' => '<p>x</p>',
            'channels'     => [$this->defaultChannelId()],
        ]);
        expect($response->getStatusCode())->toBe(403);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_update_happy_path(): void
    {
        $admin = $this->createAdmin();
        $slug = 'upd-'.uniqid();
        $id = $this->insertCmsPage(['url_key' => $slug, 'page_title' => 'Before']);

        $response = $this->adminPut($admin, '/api/admin/cms/pages/'.$id, [
            'locale'   => 'en',
            'channels' => [$this->defaultChannelId()],
            'en'       => [
                'url_key'      => $slug,
                'page_title'   => 'After Update',
                'html_content' => '<p>new</p>',
            ],
        ]);

        $response->assertOk();
        expect(\DB::table('cms_page_translations')->where('cms_page_id', $id)->where('locale', 'en')->value('page_title'))->toBe('After Update');
    }

    public function test_update_duplicate_url_key_returns_422(): void
    {
        $admin = $this->createAdmin();
        $slug1 = 'upd-other-'.uniqid();
        $this->insertCmsPage(['url_key' => $slug1]);
        $id2 = $this->insertCmsPage(['url_key' => 'upd-target-'.uniqid()]);

        $response = $this->adminPut($admin, '/api/admin/cms/pages/'.$id2, [
            'locale'   => 'en',
            'channels' => [$this->defaultChannelId()],
            'en'       => [
                'url_key'      => $slug1,
                'page_title'   => 'Stealing',
                'html_content' => '<p>x</p>',
            ],
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_same_url_key_on_self_is_ok(): void
    {
        $admin = $this->createAdmin();
        $slug = 'self-'.uniqid();
        $id = $this->insertCmsPage(['url_key' => $slug]);

        $response = $this->adminPut($admin, '/api/admin/cms/pages/'.$id, [
            'locale'   => 'en',
            'channels' => [$this->defaultChannelId()],
            'en'       => [
                'url_key'      => $slug,
                'page_title'   => 'Self Slug',
                'html_content' => '<p>x</p>',
            ],
        ]);

        $response->assertOk();
    }

    public function test_update_sync_channels(): void
    {
        $admin = $this->createAdmin();
        $cid = $this->defaultChannelId();
        $id = $this->insertCmsPage(['url_key' => 'sync-'.uniqid()], [$cid]);

        $response = $this->adminPut($admin, '/api/admin/cms/pages/'.$id, [
            'locale'   => 'en',
            'channels' => [$cid],
            'en'       => [
                'url_key'      => 'sync-'.uniqid(),
                'page_title'   => 'X',
                'html_content' => '<p>x</p>',
            ],
        ]);

        $response->assertOk();
        $get = $this->adminGet($admin, '/api/admin/cms/pages/'.$id);
        $get->assertOk();
        expect(collect($get->json('channels'))->pluck('id')->all())->toContain($cid);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/cms/pages/9999999', [
            'locale'   => 'en',
            'channels' => [$this->defaultChannelId()],
            'en'       => ['url_key' => 'nf-'.uniqid(), 'page_title' => 'X', 'html_content' => '<p>x</p>'],
        ]);
        expect($response->getStatusCode())->toBe(404);
    }

    public function test_update_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertCmsPage(['url_key' => 'unu-'.uniqid()]);
        $response = $this->putJson('/api/admin/cms/pages/'.$id, [
            'locale'   => 'en',
            'channels' => [1],
            'en'       => ['url_key' => 'x', 'page_title' => 'y', 'html_content' => 'z'],
        ]);
        expect($response->getStatusCode())->toBe(401);
    }

    public function test_update_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertCmsPage(['url_key' => 'unp-'.uniqid()]);

        $response = $this->adminPut($admin, '/api/admin/cms/pages/'.$id, [
            'locale'   => 'en',
            'channels' => [$this->defaultChannelId()],
            'en'       => ['url_key' => 'unp-x-'.uniqid(), 'page_title' => 'X', 'html_content' => '<p>x</p>'],
        ]);
        expect($response->getStatusCode())->toBe(403);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertCmsPage(['url_key' => 'del-'.uniqid()]);

        $response = $this->adminDelete($admin, '/api/admin/cms/pages/'.$id);

        // No-body delete — expect 204 (or 200 if API Platform decides otherwise)
        expect(in_array($response->getStatusCode(), [204, 200]))->toBeTrue();
        expect(\DB::table('cms_pages')->where('id', $id)->exists())->toBeFalse();

        $check = $this->adminGet($admin, '/api/admin/cms/pages/'.$id);
        $check->assertStatus(404);
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/cms/pages/9999999');
        expect($response->getStatusCode())->toBe(404);
    }

    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertCmsPage(['url_key' => 'dna-'.uniqid()]);
        $response = $this->deleteJson('/api/admin/cms/pages/'.$id);
        expect($response->getStatusCode())->toBe(401);
    }

    public function test_delete_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertCmsPage(['url_key' => 'dnp-'.uniqid()]);

        $response = $this->adminDelete($admin, '/api/admin/cms/pages/'.$id);
        expect($response->getStatusCode())->toBe(403);
    }

    // -------------------------------------------------------------------------
    // Mass delete
    // -------------------------------------------------------------------------

    public function test_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCmsPage(['url_key' => 'md1-'.uniqid()]);
        $id2 = $this->insertCmsPage(['url_key' => 'md2-'.uniqid()]);

        $response = $this->adminPost($admin, '/api/admin/cms/pages/mass-delete', [
            'indices' => [$id1, $id2],
        ]);

        $response->assertOk();
        expect($response->json('deleted'))->toBeArray();
        expect(\DB::table('cms_pages')->where('id', $id1)->exists())->toBeFalse();
        expect(\DB::table('cms_pages')->where('id', $id2)->exists())->toBeFalse();
    }

    public function test_mass_delete_mixed_valid_invalid_ids_silently_skips_unknown(): void
    {
        $admin = $this->createAdmin();
        $id1 = $this->insertCmsPage(['url_key' => 'mv1-'.uniqid()]);

        $response = $this->adminPost($admin, '/api/admin/cms/pages/mass-delete', [
            'indices' => [$id1, 99999999],
        ]);

        $response->assertOk();
        $deleted = $response->json('deleted');
        expect($deleted)->toContain($id1);
        expect(count($deleted))->toBe(1);
    }

    public function test_mass_delete_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/cms/pages/mass-delete', ['indices' => []]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_mass_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/cms/pages/mass-delete', ['indices' => [99]]);
        expect($response->getStatusCode())->toBe(401);
    }

    public function test_mass_delete_no_permission_returns_403(): void
    {
        $admin = $this->createAdminWithoutPermissions();
        $id = $this->insertCmsPage(['url_key' => 'mnp-'.uniqid()]);

        $response = $this->adminPost($admin, '/api/admin/cms/pages/mass-delete', [
            'indices' => [$id],
        ]);
        expect($response->getStatusCode())->toBe(403);
    }
}

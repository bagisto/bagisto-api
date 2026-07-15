<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Admin;

/**
 * REST coverage for Admin Settings → Themes (Block B Wave 2).
 *
 * Endpoints:
 *   GET    /api/admin/settings/themes
 *   GET    /api/admin/settings/themes/{id}
 *   POST   /api/admin/settings/themes
 *   PUT    /api/admin/settings/themes/{id}
 *   DELETE /api/admin/settings/themes/{id}
 *   POST   /api/admin/settings/themes/mass-delete
 *   POST   /api/admin/settings/themes/mass-update-status
 */
class SettingsThemeTest extends AdminApiTestCase
{
    protected function channelId(): int
    {
        $id = \DB::table('channels')->orderBy('id')->value('id');

        return (int) $id;
    }

    protected function insertTheme(array $overrides = []): int
    {
        return (int) \DB::table('theme_customizations')->insertGetId(array_merge([
            'name' => 'Test Theme '.rand(1000, 9999),
            'type' => 'static_content',
            'sort_order' => 1,
            'status' => 1,
            'channel_id' => $this->channelId(),
            'theme_code' => 'default',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    protected function insertThemeTranslation(int $themeId, string $locale, array $options): int
    {
        return (int) \DB::table('theme_customization_translations')->insertGetId([
            'theme_customization_id' => $themeId,
            'locale' => $locale,
            'options' => json_encode($options),
        ]);
    }

    protected function adminPut(Admin $admin, string $url, array $data = [], ?string $token = null): TestResponse
    {
        return $this->putJson($url, $data, $this->adminHeaders($admin, $token));
    }

    protected function adminDelete(Admin $admin, string $url, ?string $token = null): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin, $token));
    }

    public function test_listing_requires_admin_token(): void
    {
        $this->seedRequiredData();
        $response = $this->publicGet('/api/admin/settings/themes');
        $response->assertStatus(401);
    }

    public function test_create_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/settings/themes', [
            'name' => 'X', 'sort_order' => 1, 'type' => 'static_content',
            'channel_id' => $this->channelId(), 'theme_code' => 'default',
        ]);
        $response->assertStatus(401);
    }

    public function test_detail_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertTheme();
        $response = $this->publicGet('/api/admin/settings/themes/'.$id);
        $response->assertStatus(401);
    }

    public function test_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $id = $this->insertTheme();
        $response = $this->deleteJson('/api/admin/settings/themes/'.$id);
        expect($response->getStatusCode())->toBe(401);
    }

    public function test_listing_returns_envelope(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/themes');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']);
        expect($response->json('meta.currentPage'))->toBe(1);
        expect($response->json('meta.perPage'))->toBe(10);
    }

    public function test_listing_returns_seeded_row(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTheme(['name' => 'ListingMarkerTheme', 'type' => 'image_carousel']);

        $response = $this->adminGet($admin, '/api/admin/settings/themes?per_page=50');
        $response->assertOk();

        $row = collect($response->json('data'))->firstWhere('id', $id);
        expect($row)->not()->toBeNull();
        expect($row['name'])->toBe('ListingMarkerTheme');
        expect($row['type'])->toBe('image_carousel');
        expect($row)->toHaveKeys(['id', 'name', 'type', 'sortOrder', 'status', 'channelId', 'themeCode', 'createdAt', 'updatedAt']);
    }

    public function test_listing_filter_by_type(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertTheme(['type' => 'image_carousel']);
        $b = $this->insertTheme(['type' => 'static_content']);

        $response = $this->adminGet($admin, '/api/admin/settings/themes?type=image_carousel&per_page=50');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($a);
        expect($ids)->not()->toContain($b);
    }

    public function test_listing_filter_by_name(): void
    {
        $admin = $this->createAdmin();
        $marker = 'NameFilter'.rand(1000, 9999);
        $a = $this->insertTheme(['name' => $marker.' One']);
        $b = $this->insertTheme(['name' => 'Other Theme']);

        $response = $this->adminGet($admin, '/api/admin/settings/themes?name='.$marker.'&per_page=50');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($a);
        expect($ids)->not()->toContain($b);
    }

    public function test_listing_filter_by_channel(): void
    {
        $admin = $this->createAdmin();
        $cid = $this->channelId();
        $a = $this->insertTheme(['channel_id' => $cid]);

        $response = $this->adminGet($admin, '/api/admin/settings/themes?channel_id='.$cid.'&per_page=50');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($a);
    }

    public function test_listing_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        $enabled = $this->insertTheme(['status' => 1]);
        $disabled = $this->insertTheme(['status' => 0]);

        $response = $this->adminGet($admin, '/api/admin/settings/themes?status=0&per_page=50');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        expect($ids)->toContain($disabled);
        expect($ids)->not()->toContain($enabled);
    }

    public function test_listing_sort_by_name_asc(): void
    {
        $admin = $this->createAdmin();
        $this->insertTheme(['name' => 'ZZZ Theme '.rand(1, 999)]);
        $this->insertTheme(['name' => 'AAA Theme '.rand(1, 999)]);

        $response = $this->adminGet($admin, '/api/admin/settings/themes?sort=name&order=asc&per_page=50');
        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();
        $sorted = $names;
        sort($sorted, SORT_STRING);
        expect($names)->toBe($sorted);
    }

    public function test_listing_per_page_clamped(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/themes?per_page=9999');
        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    public function test_detail_returns_row_with_translations(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTheme(['name' => 'DetailTheme', 'type' => 'static_content']);
        $this->insertThemeTranslation($id, 'en', ['html' => '<p>Hello</p>', 'css' => '.x{}']);

        $response = $this->adminGet($admin, '/api/admin/settings/themes/'.$id);
        $response->assertOk();
        expect($response->json('id'))->toBe($id);
        expect($response->json('name'))->toBe('DetailTheme');
        expect($response->json('translations'))->toBeArray();

        $en = collect($response->json('translations'))->firstWhere('locale', 'en');
        expect($en)->not()->toBeNull();
        expect($en['options']['html'])->toBe('<p>Hello</p>');
    }

    public function test_detail_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/settings/themes/9999999');
        $response->assertStatus(404);
    }

    public function test_create_happy_path_returns_201(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/themes', [
            'name' => 'CreatedTheme',
            'sort_order' => 5,
            'type' => 'image_carousel',
            'channel_id' => $this->channelId(),
            'theme_code' => 'default',
            'status' => true,
        ]);

        $response->assertStatus(201);
        expect($response->json('id'))->toBeInt();
        expect($response->json('name'))->toBe('CreatedTheme');
        expect(\DB::table('theme_customizations')->where('id', $response->json('id'))->exists())->toBeTrue();
    }

    public function test_create_missing_name_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/themes', [
            'sort_order' => 1, 'type' => 'static_content',
            'channel_id' => $this->channelId(), 'theme_code' => 'default',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_missing_sort_order_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/themes', [
            'name' => 'X', 'type' => 'static_content',
            'channel_id' => $this->channelId(), 'theme_code' => 'default',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_invalid_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/themes', [
            'name' => 'X', 'sort_order' => 1, 'type' => 'nope_invalid',
            'channel_id' => $this->channelId(), 'theme_code' => 'default',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_unknown_channel_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/themes', [
            'name' => 'X', 'sort_order' => 1, 'type' => 'static_content',
            'channel_id' => 999999, 'theme_code' => 'default',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_missing_theme_code_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/themes', [
            'name' => 'X', 'sort_order' => 1, 'type' => 'static_content',
            'channel_id' => $this->channelId(),
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_changes_name(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTheme(['name' => 'OldName']);

        $response = $this->adminPut($admin, '/api/admin/settings/themes/'.$id, [
            'name' => 'NewName',
            'sort_order' => 1,
            'type' => 'static_content',
            'channel_id' => $this->channelId(),
            'theme_code' => 'default',
        ]);
        $response->assertOk();
        expect(\DB::table('theme_customizations')->where('id', $id)->value('name'))->toBe('NewName');
    }

    public function test_update_writes_per_locale_options(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTheme(['type' => 'static_content']);

        $response = $this->adminPut($admin, '/api/admin/settings/themes/'.$id, [
            'name' => 'WithOptions',
            'sort_order' => 1,
            'type' => 'static_content',
            'channel_id' => $this->channelId(),
            'theme_code' => 'default',
            'locale' => 'en',
            'options' => ['html' => '<p>Hi</p>', 'css' => '.a{}'],
        ]);
        $response->assertOk();

        $tr = \DB::table('theme_customization_translations')
            ->where('theme_customization_id', $id)
            ->where('locale', 'en')
            ->first();
        expect($tr)->not()->toBeNull();
        $opts = json_decode($tr->options, true);
        expect($opts['html'])->toBe('<p>Hi</p>');
    }

    public function test_update_strips_script_tags_in_static_content(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTheme(['type' => 'static_content']);

        $this->adminPut($admin, '/api/admin/settings/themes/'.$id, [
            'name' => 'X',
            'sort_order' => 1,
            'type' => 'static_content',
            'channel_id' => $this->channelId(),
            'theme_code' => 'default',
            'locale' => 'en',
            'options' => ['html' => '<p>Hi</p><script>alert(1)</script>', 'css' => '.a{}'],
        ])->assertOk();

        $tr = \DB::table('theme_customization_translations')
            ->where('theme_customization_id', $id)
            ->where('locale', 'en')
            ->first();
        $opts = json_decode($tr->options, true);
        expect(str_contains($opts['html'], '<script>'))->toBeFalse();
    }

    public function test_update_invalid_type_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTheme();
        $response = $this->adminPut($admin, '/api/admin/settings/themes/'.$id, [
            'name' => 'X', 'sort_order' => 1, 'type' => 'bogus',
            'channel_id' => $this->channelId(), 'theme_code' => 'default',
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_update_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPut($admin, '/api/admin/settings/themes/9999999', [
            'name' => 'X', 'sort_order' => 1, 'type' => 'static_content',
            'channel_id' => $this->channelId(), 'theme_code' => 'default',
        ]);
        expect($response->getStatusCode())->toBe(404);
    }

    public function test_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTheme();

        $response = $this->adminDelete($admin, '/api/admin/settings/themes/'.$id);
        $response->assertOk();
        expect(\DB::table('theme_customizations')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_delete_unknown_id_returns_404(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminDelete($admin, '/api/admin/settings/themes/9999999');
        $response->assertStatus(404);
    }

    public function test_mass_delete_happy_path(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertTheme();
        $b = $this->insertTheme();

        $response = $this->adminPost($admin, '/api/admin/settings/themes/mass-delete', [
            'indices' => [$a, $b],
        ]);
        $response->assertOk();
        expect($response->json('deleted'))->toBeArray();
        expect(\DB::table('theme_customizations')->where('id', $a)->exists())->toBeFalse();
        expect(\DB::table('theme_customizations')->where('id', $b)->exists())->toBeFalse();
    }

    public function test_mass_delete_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/themes/mass-delete', ['indices' => []]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_mass_delete_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/settings/themes/mass-delete', ['indices' => [1]]);
        expect($response->getStatusCode())->toBe(401);
    }

    public function test_mass_update_status_happy_path(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertTheme(['status' => 0]);
        $b = $this->insertTheme(['status' => 0]);

        $response = $this->adminPost($admin, '/api/admin/settings/themes/mass-update-status', [
            'indices' => [$a, $b],
            'value' => 1,
        ]);
        $response->assertOk();
        expect((int) \DB::table('theme_customizations')->where('id', $a)->value('status'))->toBe(1);
        expect((int) \DB::table('theme_customizations')->where('id', $b)->value('status'))->toBe(1);
    }

    public function test_mass_update_status_invalid_value_returns_422(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTheme();
        $response = $this->adminPost($admin, '/api/admin/settings/themes/mass-update-status', [
            'indices' => [$id],
            'value' => 99,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_mass_update_status_empty_indices_returns_422(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminPost($admin, '/api/admin/settings/themes/mass-update-status', [
            'indices' => [],
            'value' => 1,
        ]);
        expect($response->getStatusCode())->toBe(422);
    }

    public function test_mass_update_status_requires_auth(): void
    {
        $this->seedRequiredData();
        $response = $this->postJson('/api/admin/settings/themes/mass-update-status', [
            'indices' => [1], 'value' => 1,
        ]);
        expect($response->getStatusCode())->toBe(401);
    }
}

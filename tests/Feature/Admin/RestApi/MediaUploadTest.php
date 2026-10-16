<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Category\Models\Category;
use Webkul\Core\Models\Channel;
use Webkul\Core\Models\Locale;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

/**
 * REST coverage for the admin media upload endpoints: category logo/banner, attribute
 * option swatch, admin avatar, channel logo/favicon and locale logo.
 */
class MediaUploadTest extends AdminApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('filesystems.default'));
        Storage::fake('public');
    }

    protected function customRoleAdmin(array $permissions = []): Admin
    {
        $role = Role::create([
            'name' => 'media-test-'.uniqid(),
            'description' => 'media-test',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        return $this->createAdmin(['role_id' => $role->id]);
    }

    protected function upload(Admin $admin, string $url, array $data): TestResponse
    {
        return $this->post($url, $data, array_merge($this->adminHeaders($admin), ['Accept' => 'application/json']));
    }

    protected function remove(Admin $admin, string $url): TestResponse
    {
        return $this->deleteJson($url, [], $this->adminHeaders($admin));
    }

    protected function category(): Category
    {
        $category = Category::create([
            'position' => 1,
            'status' => 1,
            'en' => ['name' => 'Media Cat', 'slug' => 'media-cat-'.uniqid()],
        ]);

        return $category->fresh();
    }

    protected function attributeWithOption(string $swatchType = 'image'): array
    {
        $attributeId = \DB::table('attributes')->insertGetId([
            'code' => 'media_swatch_'.uniqid(),
            'admin_name' => 'Media Swatch',
            'type' => 'select',
            'swatch_type' => $swatchType,
            'position' => 1,
            'is_required' => 0,
            'is_unique' => 0,
            'is_filterable' => 0,
            'is_comparable' => 0,
            'is_configurable' => 0,
            'is_user_defined' => 1,
            'is_visible_on_front' => 0,
            'value_per_locale' => 0,
            'value_per_channel' => 0,
            'enable_wysiwyg' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $optionId = \DB::table('attribute_options')->insertGetId([
            'attribute_id' => $attributeId,
            'admin_name' => 'Navy',
            'sort_order' => 1,
        ]);

        return [$attributeId, $optionId];
    }

    public function test_category_logo_upload_stores_webp_and_alt_text(): void
    {
        $admin = $this->createAdmin();
        $category = $this->category();

        $response = $this->upload($admin, "/api/admin/catalog/categories/{$category->id}/media", [
            'logo' => UploadedFile::fake()->image('logo.png', 100, 100),
            'alt_text' => 'Summer logo',
            'file_name' => 'summer-logo',
            'locale' => 'en',
        ]);

        $response->assertOk();
        expect($response->json('id'))->toBe($category->id);
        expect($response->json('logoUrl'))->toContain("category/{$category->id}/summer-logo.webp");
        expect($response->json('logoAlt'))->toBe('Summer logo');

        $path = Category::find($category->id)->logo_path;
        expect($path)->toBe("category/{$category->id}/summer-logo.webp");
        Storage::assertExists($path);
    }

    public function test_category_banner_upload_keeps_logo(): void
    {
        $admin = $this->createAdmin();
        $category = $this->category();
        Storage::put("category/{$category->id}/logo.webp", 'logo');
        $category->update(['logo_path' => "category/{$category->id}/logo.webp"]);

        $response = $this->upload($admin, "/api/admin/catalog/categories/{$category->id}/media", [
            'banner' => UploadedFile::fake()->image('banner.jpg', 300, 100),
        ]);

        $response->assertOk();
        expect($response->json('bannerUrl'))->toContain("category/{$category->id}/");
        expect($response->json('logoUrl'))->toContain("category/{$category->id}/logo.webp");
    }

    public function test_category_media_upload_requires_logo_or_banner(): void
    {
        $admin = $this->createAdmin();
        $category = $this->category();

        $response = $this->upload($admin, "/api/admin/catalog/categories/{$category->id}/media", [
            'alt_text' => 'Nothing to attach',
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_category_media_upload_rejects_non_image(): void
    {
        $admin = $this->createAdmin();
        $category = $this->category();

        $response = $this->upload($admin, "/api/admin/catalog/categories/{$category->id}/media", [
            'logo' => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf'),
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_category_media_delete_clears_image_and_alt(): void
    {
        $admin = $this->createAdmin();
        $category = $this->category();
        Storage::put("category/{$category->id}/logo.webp", 'logo');
        $category->update(['logo_path' => "category/{$category->id}/logo.webp"]);
        \DB::table('category_translations')->where('category_id', $category->id)->update(['logo_alt' => 'Old alt']);

        $response = $this->remove($admin, "/api/admin/catalog/categories/{$category->id}/media/logo");

        $response->assertOk();
        expect($response->json('logoUrl'))->toBeNull();
        expect($response->json('logoAlt'))->toBeNull();
        Storage::assertMissing("category/{$category->id}/logo.webp");
    }

    public function test_category_media_writes_no_url_rewrite(): void
    {
        $admin = $this->createAdmin();
        $category = $this->category();

        $upload = $this->upload($admin, "/api/admin/catalog/categories/{$category->id}/media", [
            'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
        ]);

        $upload->assertOk();

        $this->remove($admin, "/api/admin/catalog/categories/{$category->id}/media/logo")->assertOk();

        expect(\DB::table('url_rewrites')->where('request_path', $category->slug)->count())->toBe(0);
    }

    public function test_category_media_unknown_category_returns_404(): void
    {
        $response = $this->upload($this->createAdmin(), '/api/admin/catalog/categories/99999999/media', [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        expect($response->getStatusCode())->toBe(404);
    }

    public function test_category_media_requires_edit_permission(): void
    {
        $category = $this->category();

        $response = $this->upload($this->customRoleAdmin(['catalog.categories']), "/api/admin/catalog/categories/{$category->id}/media", [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        expect($response->getStatusCode())->toBe(403);
    }

    public function test_category_media_requires_token(): void
    {
        $category = $this->category();

        $response = $this->post("/api/admin/catalog/categories/{$category->id}/media", [
            'logo' => UploadedFile::fake()->image('logo.png'),
        ], ['Accept' => 'application/json']);

        expect($response->getStatusCode())->toBe(401);
    }

    public function test_option_swatch_upload_stores_file_and_alt(): void
    {
        $admin = $this->createAdmin();
        [$attributeId, $optionId] = $this->attributeWithOption();

        $response = $this->upload($admin, "/api/admin/catalog/attributes/{$attributeId}/options/{$optionId}/swatch", [
            'swatch_value' => UploadedFile::fake()->image('navy.png', 40, 40),
            'swatch_file_name' => 'navy-blue',
            'swatch_alt' => 'Navy fabric',
            'locale' => 'en',
        ]);

        $response->assertOk();

        $option = collect($response->json('options'))->firstWhere('id', $optionId);
        expect($option['swatchValue'])->toBe('attribute_option/navy-blue.png');
        expect($option['swatchFileName'])->toBe('navy-blue');
        expect($option['swatchAlt'])->toBe('Navy fabric');
        Storage::assertExists('attribute_option/navy-blue.png');
    }

    public function test_option_swatch_upload_rejects_non_image_swatch_type(): void
    {
        [$attributeId, $optionId] = $this->attributeWithOption('color');

        $response = $this->upload($this->createAdmin(), "/api/admin/catalog/attributes/{$attributeId}/options/{$optionId}/swatch", [
            'swatch_value' => UploadedFile::fake()->image('navy.png'),
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_option_swatch_upload_unknown_option_returns_404(): void
    {
        [$attributeId] = $this->attributeWithOption();

        $response = $this->upload($this->createAdmin(), "/api/admin/catalog/attributes/{$attributeId}/options/99999999/swatch", [
            'swatch_value' => UploadedFile::fake()->image('navy.png'),
        ]);

        expect($response->getStatusCode())->toBe(404);
    }

    public function test_option_swatch_upload_requires_edit_permission(): void
    {
        [$attributeId, $optionId] = $this->attributeWithOption();

        $response = $this->upload($this->customRoleAdmin(['catalog.attributes']), "/api/admin/catalog/attributes/{$attributeId}/options/{$optionId}/swatch", [
            'swatch_value' => UploadedFile::fake()->image('navy.png'),
        ]);

        expect($response->getStatusCode())->toBe(403);
    }

    public function test_user_avatar_upload_and_clear(): void
    {
        $admin = $this->createAdmin();
        $target = $this->createAdmin();

        $response = $this->upload($admin, "/api/admin/settings/users/{$target->id}/image", [
            'image' => UploadedFile::fake()->image('me.png', 80, 80),
        ]);

        $response->assertOk();
        $path = Admin::find($target->id)->image;
        expect($path)->toStartWith("admins/{$target->id}/");
        expect($response->json('image'))->toBe($path);
        Storage::assertExists($path);

        $cleared = $this->remove($admin, "/api/admin/settings/users/{$target->id}/image");

        $cleared->assertOk();
        expect($cleared->json('image'))->toBeNull();
        expect(Admin::find($target->id)->image)->toBeNull();
        Storage::assertMissing($path);
    }

    public function test_user_avatar_upload_rejects_invalid_file(): void
    {
        $target = $this->createAdmin();

        $response = $this->upload($this->createAdmin(), "/api/admin/settings/users/{$target->id}/image", [
            'image' => UploadedFile::fake()->create('me.pdf', 10, 'application/pdf'),
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_user_avatar_unknown_user_returns_404(): void
    {
        $response = $this->upload($this->createAdmin(), '/api/admin/settings/users/99999999/image', [
            'image' => UploadedFile::fake()->image('me.png'),
        ]);

        expect($response->getStatusCode())->toBe(404);
    }

    public function test_user_avatar_requires_edit_permission(): void
    {
        $target = $this->createAdmin();

        $response = $this->remove($this->customRoleAdmin(['settings.users']), "/api/admin/settings/users/{$target->id}/image");

        expect($response->getStatusCode())->toBe(403);
    }

    public function test_channel_logo_and_favicon_upload_and_remove(): void
    {
        $admin = $this->createAdmin();
        $channel = Channel::first();

        $logo = $this->upload($admin, "/api/admin/settings/channels/{$channel->id}/logo", [
            'logo' => UploadedFile::fake()->image('store.png', 120, 40),
            'file_name' => 'store-logo',
        ]);

        $logo->assertOk();
        expect($logo->json('logo'))->toBe("channel/{$channel->id}/store-logo.png");
        Storage::assertExists("channel/{$channel->id}/store-logo.png");

        $favicon = $this->upload($admin, "/api/admin/settings/channels/{$channel->id}/favicon", [
            'favicon' => UploadedFile::fake()->image('favicon.png', 16, 16),
        ]);

        $favicon->assertOk();
        expect($favicon->json('favicon'))->toStartWith("channel/{$channel->id}/");
        expect($favicon->json('logo'))->toBe("channel/{$channel->id}/store-logo.png");

        $removed = $this->remove($admin, "/api/admin/settings/channels/{$channel->id}/logo");

        $removed->assertOk();
        expect($removed->json('logo'))->toBeNull();
        expect(Channel::find($channel->id)->favicon)->not()->toBeNull();
        Storage::assertMissing("channel/{$channel->id}/store-logo.png");
    }

    public function test_channel_logo_upload_rejects_invalid_file(): void
    {
        $channel = Channel::first();

        $response = $this->upload($this->createAdmin(), "/api/admin/settings/channels/{$channel->id}/logo", [
            'logo' => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf'),
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_channel_logo_unknown_channel_returns_404(): void
    {
        $response = $this->upload($this->createAdmin(), '/api/admin/settings/channels/99999999/logo', [
            'logo' => UploadedFile::fake()->image('store.png'),
        ]);

        expect($response->getStatusCode())->toBe(404);
    }

    public function test_channel_logo_requires_edit_permission(): void
    {
        $channel = Channel::first();

        $response = $this->upload($this->customRoleAdmin(['settings.channels']), "/api/admin/settings/channels/{$channel->id}/logo", [
            'logo' => UploadedFile::fake()->image('store.png'),
        ]);

        expect($response->getStatusCode())->toBe(403);
    }

    public function test_locale_logo_upload_and_remove(): void
    {
        $admin = $this->createAdmin();
        $locale = Locale::create(['code' => 'zz'.substr(uniqid(), -4), 'name' => 'Media Locale', 'direction' => 'ltr']);

        $response = $this->upload($admin, "/api/admin/settings/locales/{$locale->id}/logo", [
            'logo' => UploadedFile::fake()->image('flag.png', 30, 20),
        ]);

        $response->assertOk();
        expect($response->json('logoPath'))->toBe("locales/{$locale->code}.png");
        Storage::assertExists("locales/{$locale->code}.png");

        $removed = $this->remove($admin, "/api/admin/settings/locales/{$locale->id}/logo");

        $removed->assertOk();
        expect($removed->json('logoPath'))->toBeNull();
        Storage::assertMissing("locales/{$locale->code}.png");
    }

    public function test_locale_logo_upload_rejects_invalid_file(): void
    {
        $locale = Locale::first();

        $response = $this->upload($this->createAdmin(), "/api/admin/settings/locales/{$locale->id}/logo", [
            'logo' => UploadedFile::fake()->create('flag.pdf', 10, 'application/pdf'),
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_locale_logo_unknown_locale_returns_404(): void
    {
        $response = $this->upload($this->createAdmin(), '/api/admin/settings/locales/99999999/logo', [
            'logo' => UploadedFile::fake()->image('flag.png'),
        ]);

        expect($response->getStatusCode())->toBe(404);
    }

    public function test_locale_logo_requires_edit_permission(): void
    {
        $locale = Locale::first();

        $response = $this->upload($this->customRoleAdmin(['settings.locales']), "/api/admin/settings/locales/{$locale->id}/logo", [
            'logo' => UploadedFile::fake()->image('flag.png'),
        ]);

        expect($response->getStatusCode())->toBe(403);
    }
}

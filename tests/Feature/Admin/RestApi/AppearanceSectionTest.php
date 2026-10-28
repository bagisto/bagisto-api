<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Http\UploadedFile;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Theme\Models\Section;
use Webkul\User\Models\Role;

/**
 * REST coverage for Admin Appearance → Sections: CRUD plus the draft workflow.
 */
class AppearanceSectionTest extends AdminApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // BACKWARD COMPATIBILITY: remove when the minimum supported core is 2.4.10.
        $this->skipUnlessCoreSupports($this->core()->hasAppearanceSections(), 'Appearance sections');
    }

    private function channelId(): int
    {
        $this->seedRequiredData();

        return (int) \DB::table('channels')->orderBy('id')->value('id');
    }

    private function makeSection(array $attributes = []): Section
    {
        $channelId = $this->channelId();

        return Section::create(array_merge([
            'name' => 'Test Section '.uniqid(),
            'type' => Section::STATIC_CONTENT,
            'theme_code' => 'default',
            'channel_id' => $channelId,
            'sort_order' => 99,
            'status' => 1,
        ], $attributes));
    }

    private function adminWithRole(string $permissionType, array $permissions = []): array
    {
        $this->seedRequiredData();

        $role = Role::create([
            'name' => 'appearance-role-'.uniqid(),
            'description' => 'test',
            'permission_type' => $permissionType,
            'permissions' => $permissions,
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);

        return [$admin, $this->adminTokenSameAsWeb($admin)];
    }

    public function test_list_returns_sections_of_the_theme(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $response = $this->adminGet($admin, '/api/admin/appearance/themes/default/sections?channel='.$section->channel_id);

        $response->assertOk();

        $ids = array_column($response->json(), 'id');

        expect($ids)->toContain($section->id);
    }

    public function test_list_pins_footer_links_last(): void
    {
        $admin = $this->createAdmin();
        $channelId = $this->channelId();

        \DB::table('theme_sections')->where('channel_id', $channelId)->delete();

        $footer = $this->makeSection(['type' => Section::FOOTER_LINKS, 'sort_order' => 1]);
        $content = $this->makeSection(['type' => Section::STATIC_CONTENT, 'sort_order' => 2]);

        $response = $this->adminGet($admin, '/api/admin/appearance/themes/default/sections?channel='.$channelId);

        $response->assertOk();

        $ids = array_column($response->json(), 'id');

        expect(array_search($footer->id, $ids, true))->toBeGreaterThan(array_search($content->id, $ids, true));
    }

    public function test_list_unknown_theme_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/appearance/themes/no-such-theme/sections');

        $response->assertNotFound();
    }

    public function test_list_requires_authentication(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet('/api/admin/appearance/themes/default/sections');

        expect($response->getStatusCode())->toBeIn([401, 403]);
    }

    public function test_get_returns_the_section(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $response = $this->adminGet($admin, '/api/admin/appearance/sections/'.$section->id);

        $response->assertOk();

        expect($response->json('id'))->toBe($section->id);
        expect($response->json('type'))->toBe(Section::STATIC_CONTENT);
        expect($response->json('hasDraft'))->toBeFalse();
        expect($response->json('isPinned'))->toBeFalse();
        expect($response->json('translations'))->toBeArray();
    }

    public function test_get_unknown_section_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/appearance/sections/99999999');

        $response->assertNotFound();
    }

    public function test_create_stages_the_section_switched_off(): void
    {
        $admin = $this->createAdmin();
        $channelId = $this->channelId();

        $response = $this->adminPost($admin, '/api/admin/appearance/themes/default/sections?channel='.$channelId, [
            'name' => 'Summer Banner',
            'type' => Section::IMAGE_CAROUSEL,
        ]);

        $response->assertSuccessful();

        $id = $response->json('id');

        expect($id)->not->toBeNull();

        $row = \DB::table('theme_sections')->where('id', $id)->first();

        expect((int) $row->status)->toBe(0);
        expect((bool) $row->draft_status)->toBeTrue();
        expect($row->theme_code)->toBe('default');
    }

    public function test_create_rejects_an_unknown_type(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/appearance/themes/default/sections', [
            'name' => 'Bad', 'type' => 'not_a_type',
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_rejects_a_second_footer_links_section(): void
    {
        $admin = $this->createAdmin();
        $channelId = $this->channelId();

        \DB::table('theme_sections')->where('channel_id', $channelId)->delete();

        $this->makeSection(['type' => Section::FOOTER_LINKS]);

        $response = $this->adminPost($admin, '/api/admin/appearance/themes/default/sections?channel='.$channelId, [
            'name' => 'Second footer',
            'type' => Section::FOOTER_LINKS,
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_create_requires_the_create_permission(): void
    {
        [$admin, $token] = $this->adminWithRole('custom', ['appearance.sections']);

        $response = $this->adminPost($admin, '/api/admin/appearance/themes/default/sections', [
            'name' => 'Nope', 'type' => Section::STATIC_CONTENT,
        ], $token);

        $response->assertForbidden();
    }

    public function test_update_writes_the_published_values(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $response = $this->putJson('/api/admin/appearance/sections/'.$section->id, [
            'name' => 'Renamed section',
            'type' => $section->type,
            'sortOrder' => 4,
            'channelId' => $section->channel_id,
            'themeCode' => 'default',
            'status' => true,
        ], $this->adminHeaders($admin));

        $response->assertOk();

        $row = \DB::table('theme_sections')->where('id', $section->id)->first();

        expect($row->name)->toBe('Renamed section');
        expect((int) $row->sort_order)->toBe(4);
    }

    public function test_update_writes_carousel_options_in_the_shape_they_are_stored(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection(['type' => Section::IMAGE_CAROUSEL]);

        $images = [
            'images' => [
                ['image' => 'storage/themes/default/sections/1/banner.webp', 'link' => '/', 'title' => 'Sale'],
            ],
        ];

        // Core reads carousel options off the request as a list of rows coming off an upload,
        // so handing it the stored shape made it read a key that is not there.
        $response = $this->putJson('/api/admin/appearance/sections/'.$section->id, [
            'name' => $section->name,
            'type' => Section::IMAGE_CAROUSEL,
            'sortOrder' => $section->sort_order,
            'channelId' => $section->channel_id,
            'themeCode' => 'default',
            'status' => true,
            'options' => $images,
        ], $this->adminHeaders($admin));

        $response->assertOk();

        // Core normalises the key order of each row, so the comparison is on content.
        expect($section->refresh()->translate('en')?->options)->toEqual($images);
    }

    public function test_update_writes_the_options_of_the_locale(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $response = $this->putJson('/api/admin/appearance/sections/'.$section->id, [
            'options' => ['css' => '.a{}', 'html' => '<p>written</p>'],
        ], $this->adminHeaders($admin));

        $response->assertOk();

        $options = $section->refresh()->translate('en')?->options;

        expect($options)->toBe(['css' => '.a{}', 'html' => '<p>written</p>']);
    }

    public function test_update_without_options_keeps_the_stored_ones(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $this->putJson('/api/admin/appearance/sections/'.$section->id, [
            'options' => ['css' => '', 'html' => '<p>kept</p>'],
        ], $this->adminHeaders($admin))->assertOk();

        $this->putJson('/api/admin/appearance/sections/'.$section->id, [
            'name' => 'Renamed only',
        ], $this->adminHeaders($admin))->assertOk();

        $options = $section->refresh()->translate('en')?->options;

        expect($options)->toBe(['css' => '', 'html' => '<p>kept</p>']);
    }

    public function test_update_unknown_section_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->putJson('/api/admin/appearance/sections/99999999', [
            'name' => 'X', 'type' => Section::STATIC_CONTENT, 'sortOrder' => 1,
            'channelId' => $this->channelId(), 'themeCode' => 'default',
        ], $this->adminHeaders($admin));

        $response->assertNotFound();
    }

    public function test_delete_removes_the_section(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $response = $this->deleteJson('/api/admin/appearance/sections/'.$section->id, [], $this->adminHeaders($admin));

        expect($response->getStatusCode())->toBeIn([200, 204]);

        $this->assertDatabaseMissing('theme_sections', ['id' => $section->id]);
    }

    public function test_delete_requires_the_delete_permission(): void
    {
        [$admin, $token] = $this->adminWithRole('custom', ['appearance.sections']);
        $section = $this->makeSection();

        $response = $this->deleteJson('/api/admin/appearance/sections/'.$section->id, [], $this->adminHeaders($admin, $token));

        $response->assertForbidden();
    }

    public function test_draft_stages_options_without_touching_published_ones(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $response = $this->adminPost($admin, '/api/admin/appearance/sections/'.$section->id.'/draft', [
            'options' => ['html' => '<p>staged</p>'],
        ]);

        $response->assertSuccessful();

        expect($response->json('hasDraft'))->toBeTrue();

        $translation = \DB::table('theme_section_translations')->where('section_id', $section->id)->first();

        expect($translation->draft_options)->not->toBeNull();
    }

    public function test_draft_without_options_returns_422(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $response = $this->adminPost($admin, '/api/admin/appearance/sections/'.$section->id.'/draft', []);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_status_stages_the_on_off_state(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection(['status' => 1]);

        $response = $this->adminPost($admin, '/api/admin/appearance/sections/'.$section->id.'/status', [
            'status' => false,
        ]);

        $response->assertSuccessful();

        $row = \DB::table('theme_sections')->where('id', $section->id)->first();

        expect((int) $row->status)->toBe(1);
        expect((bool) $row->draft_status)->toBeFalse();
    }

    public function test_publish_promotes_staged_edits(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $this->adminPost($admin, '/api/admin/appearance/sections/'.$section->id.'/draft', [
            'options' => ['html' => '<p>staged</p>'],
        ])->assertSuccessful();

        $response = $this->adminPost($admin, '/api/admin/appearance/themes/default/sections/publish?channel='.$section->channel_id, []);

        $response->assertSuccessful();

        expect($response->json('sectionIds'))->toContain($section->id);

        $translation = \DB::table('theme_section_translations')->where('section_id', $section->id)->first();

        expect($translation->draft_options)->toBeNull();
        expect($translation->options)->toContain('staged');
    }

    public function test_discard_throws_staged_edits_away(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $this->adminPost($admin, '/api/admin/appearance/sections/'.$section->id.'/draft', [
            'options' => ['html' => '<p>staged</p>'],
        ])->assertSuccessful();

        $response = $this->adminPost($admin, '/api/admin/appearance/themes/default/sections/discard?channel='.$section->channel_id, []);

        $response->assertSuccessful();

        $translation = \DB::table('theme_section_translations')->where('section_id', $section->id)->first();

        expect($translation->draft_options)->toBeNull();
    }

    public function test_reorder_keeps_footer_links_last(): void
    {
        $admin = $this->createAdmin();
        $channelId = $this->channelId();

        \DB::table('theme_sections')->where('channel_id', $channelId)->delete();

        $footer = $this->makeSection(['type' => Section::FOOTER_LINKS, 'sort_order' => 5]);
        $content = $this->makeSection(['type' => Section::STATIC_CONTENT, 'sort_order' => 6]);

        $response = $this->adminPost($admin, '/api/admin/appearance/sections/reorder', [
            'sectionIds' => [$footer->id, $content->id],
        ]);

        $response->assertSuccessful();

        expect($response->json('sectionIds'))->toBe([$content->id, $footer->id]);
    }

    public function test_reorder_without_ids_returns_422(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminPost($admin, '/api/admin/appearance/sections/reorder', []);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_duplicate_copies_the_section(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $response = $this->adminPost($admin, '/api/admin/appearance/sections/'.$section->id.'/duplicate', []);

        $response->assertSuccessful();

        expect($response->json('sourceId'))->toBe($section->id);
        expect($response->json('sectionId'))->not->toBe($section->id);

        $this->assertDatabaseHas('theme_sections', ['id' => $response->json('sectionId')]);
    }

    public function test_duplicate_refuses_a_footer_links_section(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection(['type' => Section::FOOTER_LINKS]);

        $response = $this->adminPost($admin, '/api/admin/appearance/sections/'.$section->id.'/duplicate', []);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_reorder_rejects_a_partial_list(): void
    {
        $admin = $this->createAdmin();
        $channelId = $this->channelId();

        \DB::table('theme_sections')->where('channel_id', $channelId)->delete();

        $first = $this->makeSection(['sort_order' => 1]);
        $this->makeSection(['sort_order' => 2]);

        $response = $this->adminPost($admin, '/api/admin/appearance/sections/reorder', [
            'sectionIds' => [$first->id],
        ]);

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_media_rejects_a_file_that_is_not_an_image_or_video(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $response = $this->post(
            '/api/admin/appearance/sections/'.$section->id.'/media',
            ['file' => UploadedFile::fake()->create('notes.txt', 4, 'text/plain')],
            $this->adminHeaders($admin) + ['Accept' => 'application/json'],
        );

        expect($response->getStatusCode())->toBe(422);
    }

    public function test_fields_returns_the_schema_and_options(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $response = $this->adminGet($admin, '/api/admin/appearance/sections/'.$section->id.'/fields');

        $response->assertOk();

        expect($response->json('sectionId'))->toBe($section->id);
        expect($response->json('type'))->toBe(Section::STATIC_CONTENT);
        expect($response->json('schema'))->toBeArray();
    }

    public function test_preview_applies_staged_edits(): void
    {
        $admin = $this->createAdmin();
        $channelId = $this->channelId();

        \DB::table('theme_sections')->where('channel_id', $channelId)->delete();

        $section = $this->makeSection(['status' => 1]);

        $this->adminPost($admin, '/api/admin/appearance/sections/'.$section->id.'/draft', [
            'options' => ['html' => '<p>staged</p>'],
        ])->assertSuccessful();

        $response = $this->adminGet($admin, '/api/admin/appearance/themes/default/sections/preview?channel='.$channelId);

        $response->assertOk();

        $ids = array_column($response->json('sections'), 'id');

        expect($ids)->toContain($section->id);
    }
}

<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Theme\Models\Section;
use Webkul\Theme\SectionSchema;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class AppearanceSectionTest extends AdminApiTestCase
{
    /**
     * Test query lists sections with the translations connection.
     */
    public function test_query_lists_sections_with_the_translations_connection(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $query = <<<'GQL'
            query sections($code: String!, $channel: Int) {
              adminAppearanceSections(code: $code, channel: $channel) {
                _id
                name
                type
                themeCode
                channelId
                sortOrder
                status
                draftStatus
                draftSortOrder
                hasDraft
                isPinned
                createdAt
                updatedAt
                translations {
                  edges {
                    node {
                      locale
                      options
                      draftOptions
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [
            'code' => 'default',
            'channel' => $section->channel_id,
        ], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $nodes = $response->json('data.adminAppearanceSections');

        expect($nodes)->toBeArray();
        expect(array_column($nodes, '_id'))->toContain($section->id);

        $row = collect($nodes)->firstWhere('_id', $section->id);

        expect($row['themeCode'])->toBe('default');
        expect($row['translations'])->toHaveKey('edges');
    }

    /**
     * Test query resolves a single section.
     */
    public function test_query_resolves_a_single_section(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $query = <<<'GQL'
            query getSection($id: ID!) {
              adminAppearanceSection(id: $id) {
                _id
                name
                type
                hasDraft
                isPinned
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [
            'id' => '/api/admin/appearance/sections/'.$section->id,
        ], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.adminAppearanceSection._id'))->toBe($section->id);
        expect($response->json('data.adminAppearanceSection.hasDraft'))->toBe(0);
        expect($response->json('data.adminAppearanceSection.isPinned'))->toBe(0);
    }

    /**
     * Test mutation creates a section.
     */
    public function test_mutation_creates_a_section(): void
    {
        $admin = $this->createAdmin();
        $channelId = $this->channelId();

        $mutation = <<<'GQL'
            mutation createSection($input: createAdminAppearanceSectionInput!) {
              createAdminAppearanceSection(input: $input) {
                adminAppearanceSection {
                  _id
                  name
                  type
                  status
                  draftStatus
                  hasDraft
                  isPinned
                  message
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'code' => 'default',
                'channel' => $channelId,
                'name' => 'GraphQL Banner',
                'type' => Section::IMAGE_CAROUSEL,
            ],
        ], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.createAdminAppearanceSection.adminAppearanceSection');

        expect($node['name'])->toBe('GraphQL Banner');
        expect($node['status'])->toBe(0);
        expect($node['hasDraft'])->toBe(1);
        expect($node['isPinned'])->toBe(0);

        $this->assertDatabaseHas('theme_sections', [
            'id' => $node['_id'],
            'name' => 'GraphQL Banner',
        ]);
    }

    /**
     * Test mutation rejects an unknown type.
     */
    public function test_mutation_rejects_an_unknown_type(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation createSection($input: createAdminAppearanceSectionInput!) {
              createAdminAppearanceSection(input: $input) {
                adminAppearanceSection { _id }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'code' => 'default',
                'name' => 'Bad',
                'type' => 'not_a_type',
            ],
        ], $admin);

        expect($response->json('errors'))->not->toBeNull();
    }

    /**
     * Test mutation updates a section.
     */
    public function test_mutation_updates_a_section(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $mutation = <<<'GQL'
            mutation updateSection($input: updateAdminAppearanceSectionInput!) {
              updateAdminAppearanceSection(input: $input) {
                adminAppearanceSection {
                  _id
                  name
                  sortOrder
                  hasDraft
                  isPinned
                  message
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'id' => '/api/admin/appearance/sections/'.$section->id,
                'name' => 'Renamed over GraphQL',
                'type' => $section->type,
                'sortOrder' => 7,
                'channelId' => $section->channel_id,
                'themeCode' => 'default',
            ],
        ], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.updateAdminAppearanceSection.adminAppearanceSection.name'))->toBe('Renamed over GraphQL');
        expect($response->json('data.updateAdminAppearanceSection.adminAppearanceSection.hasDraft'))->toBeIn([0, 1]);
        expect($response->json('data.updateAdminAppearanceSection.adminAppearanceSection.isPinned'))->toBe(0);

        $this->assertDatabaseHas('theme_sections', [
            'id' => $section->id,
            'name' => 'Renamed over GraphQL',
        ]);
    }

    /**
     * Test mutation deletes a section and returns the snapshot.
     */
    public function test_mutation_deletes_a_section_and_returns_the_snapshot(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $mutation = <<<'GQL'
            mutation deleteSection($input: deleteAdminAppearanceSectionInput!) {
              deleteAdminAppearanceSection(input: $input) {
                adminAppearanceSection {
                  id
                  _id
                  name
                  message
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/appearance/sections/'.$section->id],
        ], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.deleteAdminAppearanceSection.adminAppearanceSection');

        expect($node)->not->toBeNull();
        expect($node['_id'])->toBe($section->id);
        expect($node['name'])->toBe($section->name);
        expect($node['message'])->toBe(trans('bagistoapi::app.admin.appearance.section.deleted'));

        $this->assertDatabaseMissing('theme_sections', ['id' => $section->id]);
    }

    /**
     * Test mutation stages a draft.
     */
    public function test_mutation_stages_a_draft(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $mutation = <<<'GQL'
            mutation saveDraft($input: createAdminAppearanceSectionDraftInput!) {
              createAdminAppearanceSectionDraft(input: $input) {
                adminAppearanceSectionDraft {
                  sectionId
                  locale
                  hasDraft
                  message
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'sectionId' => $section->id,
                'options' => ['html' => '<p>staged</p>'],
            ],
        ], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.createAdminAppearanceSectionDraft.adminAppearanceSectionDraft');

        expect($node['sectionId'])->toBe($section->id);
        expect($node['hasDraft'])->toBeTrue();
    }

    /**
     * Test mutation stages status then publishes.
     */
    public function test_mutation_stages_status_then_publishes(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection(['status' => 1]);

        $statusMutation = <<<'GQL'
            mutation stageStatus($input: createAdminAppearanceSectionStatusInput!) {
              createAdminAppearanceSectionStatus(input: $input) {
                adminAppearanceSectionStatus {
                  sectionId
                  draftStatus
                  hasDraft
                }
              }
            }
        GQL;

        $this->adminGraphQL($statusMutation, [
            'input' => [
                'sectionId' => $section->id,
                'status' => false,
            ],
        ], $admin)->assertOk();

        $publishMutation = <<<'GQL'
            mutation publish($input: createAdminAppearanceSectionPublishInput!) {
              createAdminAppearanceSectionPublish(input: $input) {
                adminAppearanceSectionPublish {
                  themeCode
                  channelId
                  sectionIds
                  message
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($publishMutation, [
            'input' => [
                'code' => 'default',
                'channel' => $section->channel_id,
            ],
        ], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $row = \DB::table('theme_sections')->where('id', $section->id)->first();

        expect((int) $row->status)->toBe(0);
        expect($row->draft_status)->toBeNull();
    }

    /**
     * Test query fields returns the schema.
     */
    public function test_query_fields_returns_the_schema(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $query = <<<'GQL'
            query sectionFields($sectionId: Int!) {
              adminAppearanceSectionFields(sectionId: $sectionId) {
                sectionId
                type
                locale
                schema
                options
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['sectionId' => $section->id], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.adminAppearanceSectionFields.sectionId'))->toBe($section->id);
        expect($response->json('data.adminAppearanceSectionFields.type'))->toBe(Section::STATIC_CONTENT);
    }

    /**
     * Test query preview resolves.
     */
    public function test_query_preview_resolves(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection(['status' => 1]);

        $query = <<<'GQL'
            query preview($code: String!, $channel: Int) {
              adminAppearanceSectionPreview(code: $code, channel: $channel) {
                themeCode
                channelId
                locale
                sections
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [
            'code' => 'default',
            'channel' => $section->channel_id,
        ], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.adminAppearanceSectionPreview.themeCode'))->toBe('default');
    }

    /**
     * Test query requires authentication.
     */
    public function test_query_requires_authentication(): void
    {
        $this->seedRequiredData();

        $query = <<<'GQL'
            query {
              adminAppearanceSections(code: "default") {
                _id
              }
            }
        GQL;

        $response = $this->adminGraphQL($query);

        expect($response->getStatusCode() === 401 || $response->json('errors') !== null)->toBeTrue();
    }

    /**
     * The discard mutation throws the staged edits of the theme and channel away.
     */
    public function test_discard_mutation_throws_staged_edits_away(): void
    {
        Storage::fake();

        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $this->stageDraft($section, $admin);

        $response = $this->runDiscard([
            'code' => 'default',
            'channel' => $section->channel_id,
        ], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.createAdminAppearanceSectionDiscard.adminAppearanceSectionDiscard');

        expect($node['themeCode'])->toBe('default');
        expect($node['channelId'])->toBe($section->channel_id);
        expect($node['sectionIds'])->toContain($section->id);
        expect($node['message'])->toBe(trans('bagistoapi::app.admin.appearance.section.discarded'));

        $translation = \DB::table('theme_section_translations')->where('section_id', $section->id)->first();

        expect($translation->draft_options)->toBeNull();
    }

    /**
     * The discard mutation refuses a theme that is not installed.
     */
    public function test_discard_mutation_rejects_an_unknown_theme(): void
    {
        $admin = $this->createAdmin();

        $response = $this->runDiscard(['code' => 'no-such-theme-'.uniqid()], $admin);

        expect($response->json('errors'))->not->toBeNull();
        expect($response->json('data.createAdminAppearanceSectionDiscard'))->toBeNull();
    }

    /**
     * The discard mutation refuses a request without an admin token.
     */
    public function test_discard_mutation_requires_authentication(): void
    {
        $section = $this->makeSection();

        $response = $this->runDiscard([
            'code' => 'default',
            'channel' => $section->channel_id,
        ]);

        expect($response->getStatusCode() === 401 || $response->json('errors') !== null)->toBeTrue();
    }

    /**
     * The discard mutation refuses a role without the section edit permission and keeps the draft.
     */
    public function test_discard_mutation_requires_the_edit_permission(): void
    {
        Storage::fake();

        $section = $this->makeSection();

        $this->stageDraft($section, $this->createAdmin());

        [$admin, $token] = $this->adminWithAppearancePermissions(['appearance', 'appearance.sections']);

        $response = $this->runDiscard([
            'code' => 'default',
            'channel' => $section->channel_id,
        ], $admin, $token);

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.appearance.no-permission'));

        $translation = \DB::table('theme_section_translations')->where('section_id', $section->id)->first();

        expect($translation->draft_options)->not->toBeNull();
    }

    /**
     * The duplicate mutation copies the section, switched off, into the same theme and channel.
     */
    public function test_duplicate_mutation_copies_the_section(): void
    {
        Storage::fake();

        $admin = $this->createAdmin();
        $section = $this->makeSection();

        $response = $this->runDuplicate($section->id, $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.createAdminAppearanceSectionDuplicate.adminAppearanceSectionDuplicate');

        expect($node['sourceId'])->toBe($section->id);
        expect($node['sectionId'])->not->toBe($section->id);
        expect($node['type'])->toBe(Section::STATIC_CONTENT);
        expect($node['message'])->toBe(trans('bagistoapi::app.admin.appearance.section.duplicated'));

        $copy = \DB::table('theme_sections')->where('id', $node['sectionId'])->first();

        expect($copy)->not->toBeNull();
        expect($copy->theme_code)->toBe('default');
        expect((int) $copy->channel_id)->toBe($section->channel_id);
        expect((int) $copy->status)->toBe(0);
    }

    /**
     * The duplicate mutation refuses to copy a footer links section.
     */
    public function test_duplicate_mutation_refuses_a_footer_links_section(): void
    {
        $admin = $this->createAdmin();
        $section = $this->makeSection(['type' => Section::FOOTER_LINKS]);
        $before = \DB::table('theme_sections')->count();

        $response = $this->runDuplicate($section->id, $admin);

        expect($response->json('errors.0.message'))->toBe(trans('admin::app.appearance.sections.create.singleton-exists', [
            'type' => app(SectionSchema::class)->type('default', Section::FOOTER_LINKS)->getTitle(),
        ]));
        expect(\DB::table('theme_sections')->count())->toBe($before);
    }

    /**
     * The duplicate mutation refuses a request without an admin token.
     */
    public function test_duplicate_mutation_requires_authentication(): void
    {
        $section = $this->makeSection();
        $before = \DB::table('theme_sections')->count();

        $response = $this->runDuplicate($section->id);

        expect($response->getStatusCode() === 401 || $response->json('errors') !== null)->toBeTrue();
        expect(\DB::table('theme_sections')->count())->toBe($before);
    }

    /**
     * The duplicate mutation refuses a role without the section create permission.
     */
    public function test_duplicate_mutation_requires_the_create_permission(): void
    {
        $section = $this->makeSection();
        $before = \DB::table('theme_sections')->count();

        [$admin, $token] = $this->adminWithAppearancePermissions([
            'appearance',
            'appearance.sections',
            'appearance.sections.edit',
        ]);

        $response = $this->runDuplicate($section->id, $admin, $token);

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.appearance.no-permission'));
        expect(\DB::table('theme_sections')->count())->toBe($before);
    }

    /**
     * The reorder mutation stages the new order and keeps footer links last.
     */
    public function test_reorder_mutation_stages_the_order_with_footer_links_last(): void
    {
        $admin = $this->createAdmin();
        $channelId = $this->channelId();

        \DB::table('theme_sections')->where('channel_id', $channelId)->where('theme_code', 'default')->delete();

        $footer = $this->makeSection([
            'type' => Section::FOOTER_LINKS,
            'sort_order' => 5,
        ]);
        $content = $this->makeSection(['sort_order' => 6]);

        $response = $this->runReorder([$footer->id, $content->id], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $node = $response->json('data.createAdminAppearanceSectionReorder.adminAppearanceSectionReorder');

        expect($node['sectionIds'])->toBe([$content->id, $footer->id]);
        expect($node['message'])->toBe(trans('bagistoapi::app.admin.appearance.section.order-staged'));

        expect((int) \DB::table('theme_sections')->where('id', $content->id)->value('draft_sort_order'))->toBe(1);
        expect($node['hasDraft'][(string) $content->id])->toBeTrue();
    }

    /**
     * The reorder mutation refuses an empty list of sections.
     */
    public function test_reorder_mutation_requires_section_ids(): void
    {
        $admin = $this->createAdmin();

        $response = $this->runReorder([], $admin);

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.appearance.section.section-ids-required'));
    }

    /**
     * The reorder mutation refuses a request without an admin token.
     */
    public function test_reorder_mutation_requires_authentication(): void
    {
        $section = $this->makeSection();

        $response = $this->runReorder([$section->id]);

        expect($response->getStatusCode() === 401 || $response->json('errors') !== null)->toBeTrue();
        expect(\DB::table('theme_sections')->where('id', $section->id)->value('draft_sort_order'))->toBeNull();
    }

    /**
     * The reorder mutation refuses a role without the section edit permission.
     */
    public function test_reorder_mutation_requires_the_edit_permission(): void
    {
        $section = $this->makeSection();

        [$admin, $token] = $this->adminWithAppearancePermissions(['appearance', 'appearance.sections']);

        $response = $this->runReorder([$section->id], $admin, $token);

        expect($response->json('errors.0.message'))->toBe(trans('bagistoapi::app.admin.appearance.no-permission'));
        expect(\DB::table('theme_sections')->where('id', $section->id)->value('draft_sort_order'))->toBeNull();
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessCoreSupports($this->core()->hasAppearanceSections(), 'Appearance sections');
    }

    /**
     * Channel id.
     */
    private function channelId(): int
    {
        $this->seedRequiredData();

        return (int) \DB::table('channels')->orderBy('id')->value('id');
    }

    /**
     * Make section.
     */
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

    /**
     * Create an admin whose role holds the given appearance permissions, with a role-bound token.
     */
    private function adminWithAppearancePermissions(array $permissions): array
    {
        $this->seedRequiredData();

        $role = Role::create([
            'name' => 'appearance-role-'.uniqid(),
            'description' => 'test',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);

        $admin = $this->createAdmin(['role_id' => $role->id]);

        return [$admin, $this->adminAccessToken($admin)];
    }

    /**
     * Stage a draft of the section's options through the draft mutation.
     */
    private function stageDraft(Section $section, Admin $admin): void
    {
        $mutation = <<<'GQL'
            mutation saveDraft($input: createAdminAppearanceSectionDraftInput!) {
              createAdminAppearanceSectionDraft(input: $input) {
                adminAppearanceSectionDraft { hasDraft }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'sectionId' => $section->id,
                'options' => ['html' => '<p>staged</p>'],
            ],
        ], $admin);

        expect($response->json('errors'))->toBeNull();
    }

    /**
     * Run the discard mutation for the default theme on the given channel.
     */
    private function runDiscard(array $input, ?Admin $admin = null, ?string $token = null): TestResponse
    {
        $mutation = <<<'GQL'
            mutation discard($input: createAdminAppearanceSectionDiscardInput!) {
              createAdminAppearanceSectionDiscard(input: $input) {
                adminAppearanceSectionDiscard {
                  themeCode
                  channelId
                  sectionIds
                  message
                }
              }
            }
        GQL;

        return $this->adminGraphQL($mutation, ['input' => $input], $admin, $token);
    }

    /**
     * Run the duplicate mutation for the given section.
     */
    private function runDuplicate(int $sectionId, ?Admin $admin = null, ?string $token = null): TestResponse
    {
        $mutation = <<<'GQL'
            mutation duplicate($input: createAdminAppearanceSectionDuplicateInput!) {
              createAdminAppearanceSectionDuplicate(input: $input) {
                adminAppearanceSectionDuplicate {
                  sectionId
                  sourceId
                  name
                  type
                  message
                }
              }
            }
        GQL;

        return $this->adminGraphQL($mutation, ['input' => ['sectionId' => $sectionId]], $admin, $token);
    }

    /**
     * Run the reorder mutation with the given section ids.
     */
    private function runReorder(array $sectionIds, ?Admin $admin = null, ?string $token = null): TestResponse
    {
        $mutation = <<<'GQL'
            mutation reorder($input: createAdminAppearanceSectionReorderInput!) {
              createAdminAppearanceSectionReorder(input: $input) {
                adminAppearanceSectionReorder {
                  sectionIds
                  hasDraft
                  message
                }
              }
            }
        GQL;

        return $this->adminGraphQL($mutation, ['input' => ['sectionIds' => $sectionIds]], $admin, $token);
    }
}

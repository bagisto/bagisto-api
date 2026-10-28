<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Theme\Models\Section;

/**
 * GraphQL coverage for Admin Appearance → Sections: the connection, CRUD and the draft
 * workflow.
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
    }

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

        $this->assertDatabaseHas('theme_sections', ['id' => $node['_id'], 'name' => 'GraphQL Banner']);
    }

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
            'input' => ['code' => 'default', 'name' => 'Bad', 'type' => 'not_a_type'],
        ], $admin);

        expect($response->json('errors'))->not->toBeNull();
    }

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

        $this->assertDatabaseHas('theme_sections', ['id' => $section->id, 'name' => 'Renamed over GraphQL']);
    }

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

        // The snapshot is the whole point of the delete payload: asserting only that the
        // row is gone passes just as well when the payload comes back null.
        $node = $response->json('data.deleteAdminAppearanceSection.adminAppearanceSection');

        expect($node)->not->toBeNull();
        expect($node['_id'])->toBe($section->id);
        expect($node['name'])->toBe($section->name);
        expect($node['message'])->toBe(trans('bagistoapi::app.admin.appearance.section.deleted'));

        $this->assertDatabaseMissing('theme_sections', ['id' => $section->id]);
    }

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
            'input' => ['sectionId' => $section->id, 'status' => false],
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
            'input' => ['code' => 'default', 'channel' => $section->channel_id],
        ], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $row = \DB::table('theme_sections')->where('id', $section->id)->first();

        expect((int) $row->status)->toBe(0);
        expect($row->draft_status)->toBeNull();
    }

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
}

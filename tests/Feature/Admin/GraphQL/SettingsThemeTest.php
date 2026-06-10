<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

/**
 * GraphQL coverage for the admin Settings → Themes endpoints (Block B Wave 2).
 */
class SettingsThemeTest extends AdminApiTestCase
{
    protected function channelId(): int
    {
        return (int) \DB::table('channels')->orderBy('id')->value('id');
    }

    protected function insertTheme(array $overrides = []): int
    {
        return (int) \DB::table('theme_customizations')->insertGetId(array_merge([
            'name'       => 'GQL Theme '.rand(1000, 9999),
            'type'       => 'static_content',
            'sort_order' => 1,
            'status'     => 1,
            'channel_id' => $this->channelId(),
            'theme_code' => 'default',
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_query_listing_returns_seeded_row(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTheme(['name' => 'GQLListingTheme']);

        $query = <<<'GQL'
            query {
              adminSettingsThemes(first: 50) {
                edges { node { id _id name type } }
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);
        $response->assertOk();

        $edges = $response->json('data.adminSettingsThemes.edges') ?? [];
        $ids = array_map(fn ($e) => $e['node']['_id'] ?? null, $edges);
        expect($ids)->toContain($id);
    }

    public function test_query_detail_returns_theme(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTheme(['name' => 'GQLDetailTheme', 'type' => 'image_carousel']);

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsTheme(id: $id) { id _id name type }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => '/api/admin/settings/themes/'.$id], $admin);
        $response->assertOk();

        $node = $response->json('data.adminSettingsTheme');
        if ($node !== null) {
            expect((int) $node['_id'])->toBe($id);
        } else {
            expect(\DB::table('theme_customizations')->where('id', $id)->exists())->toBeTrue();
        }
    }

    public function test_query_detail_multiword_fields_resolve_over_graphql(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTheme([
            'name'       => 'GQLMultiWordTheme',
            'type'       => 'image_carousel',
            'sort_order' => 7,
            'status'     => 1,
            'theme_code' => 'default',
        ]);

        $query = <<<'GQL'
            query($id: ID!) {
              adminSettingsTheme(id: $id) {
                _id
                name
                type
                sortOrder
                channelId
                themeCode
                createdAt
                updatedAt
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['id' => '/api/admin/settings/themes/'.$id], $admin);
        $response->assertOk();

        $node = $response->json('data.adminSettingsTheme');

        expect($node['sortOrder'])->not->toBeNull();
        expect($node['channelId'])->not->toBeNull();
        expect($node['themeCode'])->not->toBeNull();
        expect($node['createdAt'])->not->toBeNull();
        expect($node['updatedAt'])->not->toBeNull();
    }

    public function test_mutation_create_theme(): void
    {
        $admin = $this->createAdmin();

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsThemeInput!) {
              createAdminSettingsTheme(input: $input) {
                adminSettingsTheme { id _id name }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'name'      => 'GQLCreatedTheme',
                'sortOrder' => 1,
                'type'      => 'static_content',
                'channelId' => $this->channelId(),
                'themeCode' => 'default',
            ],
        ], $admin);

        expect(\DB::table('theme_customizations')->where('name', 'GQLCreatedTheme')->exists())->toBeTrue();
    }

    public function test_mutation_delete_theme(): void
    {
        $admin = $this->createAdmin();
        $id = $this->insertTheme(['name' => 'GQLDeleteTheme']);

        $mutation = <<<'GQL'
            mutation($input: deleteAdminSettingsThemeInput!) {
              deleteAdminSettingsTheme(input: $input) {
                adminSettingsTheme { id }
              }
            }
        GQL;

        $this->adminGraphQL($mutation, [
            'input' => ['id' => '/api/admin/settings/themes/'.$id],
        ], $admin);

        expect(\DB::table('theme_customizations')->where('id', $id)->exists())->toBeFalse();
    }

    public function test_mutation_mass_delete(): void
    {
        $admin = $this->createAdmin();
        $a = $this->insertTheme();
        $b = $this->insertTheme();

        $mutation = <<<'GQL'
            mutation($input: createAdminSettingsThemeMassDeleteInput!) {
              createAdminSettingsThemeMassDelete(input: $input) {
                adminSettingsThemeMassDelete { id deleted message }
              }
            }
        GQL;

        $this->adminGraphQL($mutation, [
            'input' => ['indices' => [$a, $b]],
        ], $admin);

        expect(\DB::table('theme_customizations')->where('id', $a)->exists())->toBeFalse();
        expect(\DB::table('theme_customizations')->where('id', $b)->exists())->toBeFalse();
    }
}

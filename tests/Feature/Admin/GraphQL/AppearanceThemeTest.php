<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class AppearanceThemeTest extends AdminApiTestCase
{
    /**
     * Test query lists themes.
     */
    public function test_query_lists_themes(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query {
              adminAppearanceThemes {
                code
                name
                author
                version
                url
                demoUrl
                screenshot
                rating
                tags
                description
                isInstalled
                status
                activeOn
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $themes = $response->json('data.adminAppearanceThemes');

        expect($themes)->toBeArray();
        expect(count($themes))->toBeGreaterThan(0);
        expect(array_column($themes, 'code'))->toContain('default');
    }

    /**
     * Test query resolves a single theme.
     */
    public function test_query_resolves_a_single_theme(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query getTheme($code: String!) {
              adminAppearanceTheme(code: $code) {
                code
                name
                isInstalled
                status
                activeOn
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['code' => 'default'], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.adminAppearanceTheme.code'))->toBe('default');
        expect($response->json('data.adminAppearanceTheme.isInstalled'))->toBeTrue();
    }

    /**
     * Test query unknown theme returns an error.
     */
    public function test_query_unknown_theme_returns_an_error(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query getTheme($code: String!) {
              adminAppearanceTheme(code: $code) {
                code
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, ['code' => 'no-such-theme'], $admin);

        expect($response->json('errors'))->not->toBeNull();
    }

    /**
     * Test query impact resolves.
     */
    public function test_query_impact_resolves(): void
    {
        $admin = $this->createAdmin();

        $query = <<<'GQL'
            query themeImpact($code: String!, $channelIds: [Int!]!) {
              adminAppearanceThemeImpact(code: $code, channelIds: $channelIds) {
                code
                impact
              }
            }
        GQL;

        $response = $this->adminGraphQL($query, [
            'code' => 'default',
            'channelIds' => [$this->channelId()],
        ], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.adminAppearanceThemeImpact.code'))->toBe('default');
    }

    /**
     * Test mutation activates a theme.
     */
    public function test_mutation_activates_a_theme(): void
    {
        $admin = $this->createAdmin();
        $channelId = $this->channelId();

        \DB::table('channels')->where('id', $channelId)->update(['theme' => null]);

        $mutation = <<<'GQL'
            mutation activateTheme($input: createAdminAppearanceThemeActivateInput!) {
              createAdminAppearanceThemeActivate(input: $input) {
                adminAppearanceThemeActivate {
                  code
                  activatedOn
                  message
                }
              }
            }
        GQL;

        $response = $this->adminGraphQL($mutation, [
            'input' => [
                'code' => 'default',
                'channelIds' => [$channelId],
            ],
        ], $admin);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.createAdminAppearanceThemeActivate.adminAppearanceThemeActivate.code'))->toBe('default');
        expect(\DB::table('channels')->where('id', $channelId)->value('theme'))->toBe('default');
    }

    /**
     * Test query requires authentication.
     */
    public function test_query_requires_authentication(): void
    {
        $this->seedRequiredData();

        $query = <<<'GQL'
            query {
              adminAppearanceThemes {
                code
              }
            }
        GQL;

        $response = $this->adminGraphQL($query);

        expect($response->getStatusCode() === 401 || $response->json('errors') !== null)->toBeTrue();
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
}

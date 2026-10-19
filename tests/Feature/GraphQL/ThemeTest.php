<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Theme\Models\Section;

class ThemeTest extends GraphQLTestCase
{
    /**
     * Test query returns the active theme.
     */
    public function test_query_returns_the_active_theme(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL($this->queryDocument());

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $theme = $response->json('data.theme');

        expect($theme)->toBeArray();
        expect($theme['code'])->not->toBeNull();
        expect($theme['name'])->not->toBeNull();
        expect($theme['sectionTypes'])->toBe(Section::TYPES);
    }

    /**
     * Test query code matches the channel theme.
     */
    public function test_query_code_matches_the_channel_theme(): void
    {
        $this->seedRequiredData();

        $expected = \DB::table('channels')->orderBy('id')->value('theme') ?: config('themes.shop-default');

        $response = $this->graphQL($this->queryDocument());

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.theme.code'))->toBe($expected);
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
     * Query document.
     */
    private function queryDocument(): string
    {
        return <<<'GQL'
            query {
              theme {
                code
                name
                sectionTypes
              }
            }
        GQL;
    }
}

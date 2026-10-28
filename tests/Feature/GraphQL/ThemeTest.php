<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Theme\Models\Section;

/**
 * GraphQL coverage for the `theme` query.
 */
class ThemeTest extends GraphQLTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // BACKWARD COMPATIBILITY: remove when the minimum supported core is 2.4.10.
        $this->skipUnlessCoreSupports($this->core()->hasAppearanceSections(), 'Appearance sections');
    }

    private function query(): string
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

    public function test_query_returns_the_active_theme(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL($this->query());

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();

        $theme = $response->json('data.theme');

        expect($theme)->toBeArray();
        expect($theme['code'])->not->toBeNull();
        expect($theme['name'])->not->toBeNull();
        expect($theme['sectionTypes'])->toBe(Section::TYPES);
    }

    public function test_query_code_matches_the_channel_theme(): void
    {
        $this->seedRequiredData();

        $expected = \DB::table('channels')->orderBy('id')->value('theme') ?: config('themes.shop-default');

        $response = $this->graphQL($this->query());

        $response->assertOk();

        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.theme.code'))->toBe($expected);
    }
}

<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Theme\Models\Section;

/**
 * REST coverage for GET /api/shop/theme — the theme the current channel runs.
 */
class ThemeTest extends RestApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // BACKWARD COMPATIBILITY: remove when the minimum supported core is 2.4.10.
        $this->skipUnlessCoreSupports($this->core()->hasAppearanceSections(), 'Appearance sections');
    }

    private string $url = '/api/shop/theme';

    public function test_returns_the_active_theme(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->url);

        $response->assertOk();

        $body = $response->json();

        expect($body)->toBeArray();
        expect(count($body))->toBe(1);
        expect($body[0])->toHaveKeys(['code', 'name', 'sectionTypes']);
    }

    public function test_code_matches_the_channel_theme(): void
    {
        $this->seedRequiredData();

        $expected = \DB::table('channels')->orderBy('id')->value('theme') ?: config('themes.shop-default');

        $response = $this->publicGet($this->url);

        $response->assertOk();

        expect($response->json('0.code'))->toBe($expected);
    }

    public function test_falls_back_to_the_default_theme_when_the_channel_has_none(): void
    {
        $this->seedRequiredData();

        \DB::table('channels')->update(['theme' => null]);

        $response = $this->publicGet($this->url);

        $response->assertOk();

        expect($response->json('0.code'))->toBe(config('themes.shop-default'));
    }

    public function test_lists_every_section_type(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->url);

        $response->assertOk();

        expect($response->json('0.sectionTypes'))->toBe(Section::TYPES);
    }

    public function test_name_is_resolved_from_the_theme_config(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->url);

        $response->assertOk();

        $code = $response->json('0.code');

        expect($response->json('0.name'))->toBe(config('themes.shop.'.$code.'.name') ?? $code);
    }
}

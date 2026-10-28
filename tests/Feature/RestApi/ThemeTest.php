<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Theme\Models\Section;

class ThemeTest extends RestApiTestCase
{
    /**
     * The url.
     */
    private string $url = '/api/shop/theme';

    /**
     * Test returns the active theme.
     */
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

    /**
     * Test code matches the channel theme.
     */
    public function test_code_matches_the_channel_theme(): void
    {
        $this->seedRequiredData();

        $expected = \DB::table('channels')->orderBy('id')->value('theme') ?: config('themes.shop-default');

        $response = $this->publicGet($this->url);

        $response->assertOk();

        expect($response->json('0.code'))->toBe($expected);
    }

    /**
     * Test falls back to the default theme when the channel has none.
     */
    public function test_falls_back_to_the_default_theme_when_the_channel_has_none(): void
    {
        $this->seedRequiredData();

        \DB::table('channels')->update(['theme' => null]);

        $response = $this->publicGet($this->url);

        $response->assertOk();

        expect($response->json('0.code'))->toBe(config('themes.shop-default'));
    }

    /**
     * Test lists every section type.
     */
    public function test_lists_every_section_type(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->url);

        $response->assertOk();

        expect($response->json('0.sectionTypes'))->toBe(Section::TYPES);
    }

    /**
     * Test name is resolved from the theme config.
     */
    public function test_name_is_resolved_from_the_theme_config(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->url);

        $response->assertOk();

        $code = $response->json('0.code');

        expect($response->json('0.name'))->toBe(config('themes.shop.'.$code.'.name') ?? $code);
    }

    /**
     * The per-code theme route is API Platform's item IRI only, so it is not served.
     */
    public function test_theme_by_code_is_not_exposed(): void
    {
        $this->seedRequiredData();

        $code = $this->publicGet($this->url)->json('0.code');

        $this->publicGet('/api/shop/themes/'.$code)->assertNotFound();
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessCoreSupports($this->core()->hasAppearanceSections(), 'Appearance sections');
    }
}

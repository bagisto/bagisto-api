<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class UrlResolverTest extends RestApiTestCase
{
    private function categorySlug(): string
    {
        return (string) DB::table('category_translations')
            ->where('category_id', '!=', 1)
            ->value('slug');
    }

    public function test_a_category_slug_resolves_to_the_category(): void
    {
        $this->seedRequiredData();
        $slug = $this->categorySlug();

        if ($slug === '') {
            $this->markTestSkipped('No category with a slug in the test database.');
        }

        $response = $this->publicGet('/api/shop/url-resolver?path='.$slug);

        $response->assertOk();
        expect($response->json('0.type'))->toBe('category');
    }

    public function test_a_url_rewrite_resolves_to_a_redirect(): void
    {
        $this->seedRequiredData();
        DB::table('url_rewrites')->insert([
            'entity_type' => 'product',
            'request_path' => 'old-path-'.($suffix = uniqid()),
            'target_path' => 'new-path',
            'redirect_type' => '301',
            'locale' => app()->getLocale(),
        ]);

        $response = $this->publicGet('/api/shop/url-resolver?path=old-path-'.$suffix);

        $response->assertOk();
        expect($response->json('0.type'))->toBe('redirect');
        expect($response->json('0.targetPath'))->toBe('new-path');
        expect($response->json('0.redirectType'))->toBe(301);
    }

    public function test_an_unknown_path_returns_404(): void
    {
        $this->seedRequiredData();

        $this->publicGet('/api/shop/url-resolver?path=nothing-'.uniqid())->assertStatus(404);
    }

    public function test_a_path_that_is_not_a_slug_resolves_to_home(): void
    {
        $this->seedRequiredData();

        $this->publicGet('/api/shop/url-resolver?path=%24%24')->assertOk()->assertJsonPath('0.type', 'home');
    }

    public function test_the_path_is_required(): void
    {
        $this->seedRequiredData();

        $this->publicGet('/api/shop/url-resolver')->assertStatus(422);
    }
}

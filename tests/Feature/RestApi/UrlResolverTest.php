<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class UrlResolverTest extends RestApiTestCase
{
    /**
     * A category slug resolves to the category.
     */
    public function test_a_category_slug_resolves_to_the_category(): void
    {
        $this->seedRequiredData();

        $category = $this->channelCategory();

        if (! $category) {
            $this->markTestSkipped('No category under the channel root in the test database.');
        }

        $response = $this->publicGet('/api/shop/url-resolver?path='.$category->slug);

        $response->assertOk();

        expect($response->json('0.type'))->toBe('category')
            ->and($response->json('0.entityId'))->toBe((int) $category->category_id);
    }

    /**
     * A URL rewrite resolves to a redirect with its target and status.
     */
    public function test_a_url_rewrite_resolves_to_a_redirect(): void
    {
        $this->seedRequiredData();

        $requestPath = 'old-path-'.uniqid();

        $this->insertRewrite($requestPath, 'new-path', '301');

        $response = $this->publicGet('/api/shop/url-resolver?path='.$requestPath);

        $response->assertOk();

        expect($response->json('0.type'))->toBe('redirect')
            ->and($response->json('0.targetPath'))->toBe('new-path')
            ->and($response->json('0.redirectType'))->toBe(301);
    }

    /**
     * A path nothing matches answers 404.
     */
    public function test_an_unknown_path_returns_404(): void
    {
        $this->seedRequiredData();

        $this->publicGet('/api/shop/url-resolver?path=nothing-'.uniqid())->assertNotFound();
    }

    /**
     * A path that is not a slug resolves to the home page, as the storefront route does.
     */
    public function test_a_path_that_is_not_a_slug_resolves_to_home(): void
    {
        $this->seedRequiredData();

        $this->publicGet('/api/shop/url-resolver?path=%24%24')
            ->assertOk()
            ->assertJsonPath('0.type', 'home');
    }

    /**
     * The path is required.
     */
    public function test_the_path_is_required(): void
    {
        $this->seedRequiredData();

        $this->publicGet('/api/shop/url-resolver')->assertStatus(422);
    }

    /**
     * A category under the current channel's root, with its slug.
     */
    private function channelCategory(): ?object
    {
        return DB::table('category_translations')
            ->join('categories', 'categories.id', '=', 'category_translations.category_id')
            ->where('categories.parent_id', core()->getCurrentChannel()->root_category_id)
            ->where('category_translations.locale', app()->getLocale())
            ->select('category_translations.category_id', 'category_translations.slug')
            ->first();
    }

    /**
     * Insert a URL rewrite in the current locale.
     */
    private function insertRewrite(string $requestPath, string $targetPath, string $redirectType): void
    {
        DB::table('url_rewrites')->insert([
            'entity_type' => 'product',
            'request_path' => $requestPath,
            'target_path' => $targetPath,
            'redirect_type' => $redirectType,
            'locale' => app()->getLocale(),
        ]);
    }
}

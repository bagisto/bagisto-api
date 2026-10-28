<?php

namespace Webkul\BagistoApi\Tests\Feature;

use Illuminate\Http\Request;
use Webkul\BagistoApi\CacheProfiles\ApiAwareResponseCache;
use Webkul\BagistoApi\Tests\BagistoApiTestCase;
use Webkul\Core\Models\CoreConfig;
use Webkul\FPC\CacheProfiles\FullPageCacheProfile;

class ApiAwareResponseCacheTest extends BagistoApiTestCase
{
    /**
     * The profile.
     */
    private ApiAwareResponseCache $profile;

    /**
     * Test configured profile is built on core full page cache profile.
     */
    public function test_configured_profile_is_built_on_core_full_page_cache_profile(): void
    {
        $this->assertSame(ApiAwareResponseCache::class, config('responsecache.cache_profile'));
        $this->assertInstanceOf(FullPageCacheProfile::class, $this->profile);
    }

    /**
     * Test api and graphql requests are never cached.
     */
    public function test_api_and_graphql_requests_are_never_cached(): void
    {
        foreach (['/api', '/api/shop/products', '/api/admin/graphql', '/api/graphql', '/api/graphiql', '/graphql', '/graphiql'] as $path) {
            $request = Request::create($path);

            $this->assertFalse($this->profile->enabled($request), "{$path} must bypass the cache");
            $this->assertFalse($this->profile->shouldCacheRequest($request), "{$path} must not be cached");
        }
    }

    /**
     * Test storefront slug pages are cacheable.
     */
    public function test_storefront_slug_pages_are_cacheable(): void
    {
        $request = Request::create('/some-product-url-key');

        $this->assertTrue($this->profile->enabled($request));
        $this->assertTrue($this->profile->shouldCacheRequest($request));
    }

    /**
     * Test admin full page cache switch is honoured.
     */
    public function test_admin_full_page_cache_switch_is_honoured(): void
    {
        $this->saveSetting('enabled', '0');

        $this->assertFalse($this->profile->enabled(Request::create('/')));
    }

    /**
     * Test admin full page cache lifetime is honoured.
     */
    public function test_admin_full_page_cache_lifetime_is_honoured(): void
    {
        $this->saveSetting('lifetime', '15');

        $request = Request::create('/');

        if (method_exists($this->profile, 'cacheLifetimeInSeconds')) {
            $this->assertSame(15 * 60, $this->profile->cacheLifetimeInSeconds($request));

            return;
        }

        $until = $this->profile->cacheRequestUntil($request);

        $this->assertEqualsWithDelta(now()->addMinutes(15)->getTimestamp(), $until->getTimestamp(), 2);
    }

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config(['responsecache.enabled' => true]);

        $this->profile = app(ApiAwareResponseCache::class);
    }

    /**
     * Save setting.
     */
    private function saveSetting(string $field, mixed $value): void
    {
        CoreConfig::factory()->create([
            'code' => 'cache_management.full_page_cache.settings.'.$field,
            'value' => $value,
        ]);

        $this->forgetCoreConfigCache();
    }
}

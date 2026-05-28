<?php

namespace Webkul\BagistoApi\CacheProfiles;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\ResponseCache\CacheProfiles\CacheProfile;
use Symfony\Component\HttpFoundation\Response;

/**
 * ApiAwareResponseCache Profile
 *
 * This cache profile:
 * 1. Excludes ALL API routes from caching (API should return fresh data)
 * 2. Caches shop/storefront pages for performance
 * 3. Only caches successful (200) responses
 * 4. Respects cache bypass headers
 *
 * Benefits:
 * - APIs always return fresh data with correct content-type
 * - Shop pages are cached for speed
 * - No HTML cached for API responses
 */
class ApiAwareResponseCache implements CacheProfile
{
    /**
     * Determine if the response cache middleware is enabled
     *
     * Spatie's CacheResponse middleware only consults shouldCacheRequest() on
     * the write path. The read path (cache HIT) is governed by enabled() +
     * shouldBypass() only. The configured hasher keys cache entries by URL,
     * so without gating reads here, a logged-in customer would receive an
     * earlier guest's cached HTML (the header would show "Sign in / Sign up"
     * until the cache is manually cleared). Disabling the cache here for
     * authenticated customers/admins blocks BOTH reads and writes.
     */
    public function enabled(Request $request): bool
    {
        if (! (bool) config('responsecache.enabled', false)) {
            return false;
        }

        if (Auth::guard('customer')->check() || Auth::guard('admin')->check()) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the request should be cached.
     */
    public function shouldCacheRequest(Request $request): bool
    {
        // Don't cache API routes - they need fresh data
        if ($request->is('api/*') || $request->is('graphql*')) {
            return false;
        }

        // Don't cache non-GET requests
        if (! $request->isMethod('GET')) {
            return false;
        }

        // Don't cache requests with query parameters (search, filters, pagination)
        if ($request->getQueryString()) {
            return false;
        }

        // Don't cache if a storefront customer or admin is authenticated —
        // Bagisto uses the `customer` and `admin` guards, not the default
        // `web` guard, so $request->user() is always null here even for a
        // logged-in customer. Check both explicit guards.
        if (Auth::guard('customer')->check() || Auth::guard('admin')->check()) {
            return false;
        }

        // Never cache personalized storefront sections.
        if ($request->is('customer/*') ||
            $request->is('checkout/*') ||
            $request->is('admin/*') ||
            $request->is('api/*') ||
            $request->is('graphql*')) {
            return false;
        }

        // Cache catalog/storefront pages only.
        return $request->is('/')
            || $request->is('shop/*')
            || $request->is('categories/*')
            || $request->is('products/*');
    }

    /**
     * Determine if the response should be cached.
     *
     * Only cache successful (200) HTML responses
     */
    public function shouldCacheResponse(Response $response): bool
    {
        // Only cache successful responses
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        // Only cache HTML responses (not JSON or other formats)
        $contentType = $response->headers->get('Content-Type', '');
        if (strpos($contentType, 'text/html') === false) {
            return false;
        }

        return true;
    }

    /**
     * Return the tags to use for this cached response.
     */
    public function cacheNameSuffix(Request $request): string
    {
        return '';
    }

    /**
     * Return until when the response must be cached.
     */
    public function cacheRequestUntil(Request $request): \DateTime
    {
        return now()->addDay();
    }

    /**
     * Determine if cache name suffix should be used
     */
    public function useCacheNameSuffix(Request $request): string
    {
        return '';
    }
}

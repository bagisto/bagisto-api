<?php

namespace Webkul\BagistoApi\CacheProfiles;

use Illuminate\Http\Request;
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
     */
    public function enabled(Request $request): bool
    {
        return config('responsecache.enabled', false);
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

        // Don't cache if user is authenticated (personalized content)
        if ($request->user()) {
            return false;
        }

        // Cache only shop pages (storefront)
        if ($request->is('shop/*') ||
            $request->is('categories/*') ||
            $request->is('products/*') ||
            $request->is('*') && ! $request->is('admin/*')) {
            return true;
        }

        return false;
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

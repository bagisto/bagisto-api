<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ForceApiJson Middleware
 *
 * Ensures API responses return JSON content-type instead of HTML.
 * Works in conjunction with ApiAwareResponseCache profile:
 * - Sets Accept header to application/json if not present
 * - Ensures responses have correct JSON content-type
 * - Prevents HTML from being cached for API responses
 * - Shop pages (HTML) are still cached for speed
 */
class ForceApiJson
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip entirely for documentation/playground pages that return HTML
        if ($this->isDocumentationRoute($request)) {
            return $next($request);
        }

        // If no Accept header is set, default to JSON for API requests
        if (! $request->header('Accept') && $request->is('api/*', 'graphql*')) {
            $request->headers->set('Accept', 'application/json');
        }

        $response = $next($request);

        // Ensure API responses are JSON (for API Platform routes)
        if ($request->is('api/*', 'graphql*')) {
            if (! $response->headers->has('Content-Type') ||
                strpos($response->headers->get('Content-Type'), 'text/html') !== false) {
                $response->headers->set('Content-Type', 'application/json; charset=utf-8');
            }
        }

        return $response;
    }

    /**
     * Check if the request is for an API documentation/playground route that serves HTML.
     */
    private function isDocumentationRoute(Request $request): bool
    {
        if ($request->method() !== 'GET') {
            return false;
        }

        $path = $request->path();

        return in_array($path, [
            'api/graphiql',
            'api/graphql',
            'api',
            'api/shop',
            'api/admin',
        ]);
    }
}

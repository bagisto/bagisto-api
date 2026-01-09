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
        // If no Accept header is set, default to JSON for API requests
        // But exclude GraphQL GET requests (UI/Playground)
        if (! $request->header('Accept') && $request->is('api/*', 'graphql*')) {
            if (! ($request->path() === 'api/graphql' && $request->method() === 'GET')) {
                $request->headers->set('Accept', 'application/json');
            }
        }

        $response = $next($request);

        // Ensure API responses are JSON (for API Platform routes)
        // But exclude GraphQL GET requests (which return HTML for the UI)
        if ($request->is('api/*', 'graphql*')) {
            if (! ($request->path() === 'api/graphql' && $request->method() === 'GET')) {
                if (! $response->headers->has('Content-Type') ||
                    strpos($response->headers->get('Content-Type'), 'text/html') !== false) {
                    $response->headers->set('Content-Type', 'application/json; charset=utf-8');
                }
            }
        }

        return $response;
    }
}

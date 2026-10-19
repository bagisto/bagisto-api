<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceApiJson
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isDocumentationRoute($request)) {
            return $next($request);
        }

        if (
            ! $request->header('Accept')
            && $request->is('api/*', 'graphql*')
        ) {
            $request->headers->set('Accept', 'application/json');
        }

        $response = $next($request);

        if ($request->is('api/*', 'graphql*')) {
            if (
                ! $response->headers->has('Content-Type')
                || strpos($response->headers->get('Content-Type'), 'text/html') !== false
            ) {
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
            'api/admin/graphiql',
            'api/docs',
            'api',
            'api/shop',
            'api/admin',
        ]);
    }
}

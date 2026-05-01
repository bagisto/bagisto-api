<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Injects application/json Content-Type and an empty JSON body for bodyless
 * POST endpoints where API Platform still requires a Content-Type header.
 */
class EnsureJsonContentType
{
    /**
     * POST paths that accept no request body.
     */
    private const BODYLESS_POST_PATHS = [
        'api/shop/delete-all-compare-items',
        'api/shop/delete-all-wishlists',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (! $request->isMethod('POST')) {
            return $next($request);
        }

        if (! in_array(trim($request->path(), '/'), self::BODYLESS_POST_PATHS, true)) {
            return $next($request);
        }

        if (! $request->headers->has('Content-Type')) {
            $request->headers->set('Content-Type', 'application/json');
        }

        if (trim((string) $request->getContent()) === '') {
            $request->initialize(
                $request->query->all(),
                $request->request->all(),
                $request->attributes->all(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all(),
                '{}'
            );
            $request->headers->set('Content-Type', 'application/json');
        }

        return $next($request);
    }
}

<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reads pagination metadata stashed by PaginationHeaderNormalizer and
 * adds standard X-* headers to the outgoing response.
 *
 *   X-Total-Count   total number of items across all pages
 *   X-Page          current page (1-based)
 *   X-Per-Page      items per page
 *   X-Total-Pages   total number of pages
 *
 * Headers are added only when the normalizer captured a Paginator — i.e.
 * for paginated REST collection responses. Single-resource and non-paginated
 * collection responses are unchanged.
 */
class PaginationHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $meta = $request->attributes->get('bagistoapi.pagination');

        if (is_array($meta)) {
            $response->headers->set('X-Total-Count', (string) $meta['total']);
            $response->headers->set('X-Page', (string) $meta['page']);
            $response->headers->set('X-Per-Page', (string) $meta['per_page']);
            $response->headers->set('X-Total-Pages', (string) $meta['total_pages']);
        }

        return $response;
    }
}

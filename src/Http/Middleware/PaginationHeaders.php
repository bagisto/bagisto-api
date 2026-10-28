<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaginationHeaders
{
    /**
     * Handle the request.
     */
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

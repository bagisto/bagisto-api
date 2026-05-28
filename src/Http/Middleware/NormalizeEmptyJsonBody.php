<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Normalises an empty / whitespace-only request body to `{}` when the request
 * declares `Content-Type: application/json` (or `+json` variants).
 *
 * Symfony's JsonDecode throws `NotEncodableValueException("Syntax error")` on
 * `json_decode('')`, which API Platform surfaces as HTTP 500 before the
 * processor ever runs. This hits every admin POST/PUT/PATCH that does not
 * require a body (e.g. `POST /api/admin/logout`).
 *
 * Idempotent — non-JSON content types and non-empty bodies pass through
 * unchanged.
 */
class NormalizeEmptyJsonBody
{
    public function handle(Request $request, Closure $next)
    {
        $contentType = (string) $request->headers->get('Content-Type', '');

        if ($contentType !== '' && stripos($contentType, 'json') !== false) {
            $body = $request->getContent();

            if ($body === '' || trim($body) === '') {
                // Replace the request content with `{}` so downstream JSON
                // decoders (Symfony Serializer / API Platform) succeed.
                $request->initialize(
                    $request->query->all(),
                    $request->request->all(),
                    $request->attributes->all(),
                    $request->cookies->all(),
                    $request->files->all(),
                    $request->server->all(),
                    '{}'
                );
            }
        }

        return $next($request);
    }
}

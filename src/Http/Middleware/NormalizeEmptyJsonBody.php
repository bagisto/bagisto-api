<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Sanitises a JSON request body (`Content-Type: application/json` or a `+json`
 * variant) before API Platform's decoder runs:
 *
 * 1. Empty / whitespace-only body → replaced with `{}`. Symfony's JsonDecode
 *    throws `NotEncodableValueException("Syntax error")` on `json_decode('')`,
 *    which API Platform would surface as HTTP 500 (e.g. body-less admin actions).
 * 2. Malformed body (e.g. a trailing comma) → rejected up front with a clean
 *    HTTP 400 and a clear message, instead of a terse framework "Syntax error"
 *    (or a 500 + stack trace when APP_DEBUG is on).
 *
 * Only touches invalid bodies — a well-formed JSON body passes through unchanged,
 * and non-JSON content types (multipart, form-urlencoded, text) are ignored.
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
            } else {
                json_decode($body);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'type' => '/errors/400',
                        'title' => 'Bad Request',
                        'status' => 400,
                        'detail' => 'The request body contains invalid JSON.',
                    ], 400, ['Content-Type' => 'application/problem+json']);
                }
            }
        }

        return $next($request);
    }
}

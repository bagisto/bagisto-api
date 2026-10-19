<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NormalizeEmptyJsonBody
{
    /**
     * Handle the request.
     */
    public function handle(Request $request, Closure $next)
    {
        $contentType = (string) $request->headers->get('Content-Type', '');

        if (
            $contentType !== ''
            && stripos($contentType, 'json') !== false
        ) {
            $body = $request->getContent();

            if (
                $body === ''
                || trim($body) === ''
            ) {
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

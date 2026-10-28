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
        if ($this->isEmptyFormBody($request)) {
            $request->server->set('CONTENT_TYPE', 'application/json');
            $request->headers->set('Content-Type', 'application/json');
        }

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

    /**
     * Whether a write request has no body and no JSON type, as a bare POST from curl or a form client sends it.
     */
    protected function isEmptyFormBody(Request $request): bool
    {
        $contentType = (string) $request->headers->get('Content-Type', '');

        return in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)
            && trim($request->getContent()) === ''
            && $request->request->count() === 0
            && $request->files->count() === 0
            && (
                $contentType === ''
                || stripos($contentType, 'application/x-www-form-urlencoded') === 0
            );
    }
}

<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Exception\InvalidInputException;

/**
 * Handle InvalidInputException for REST API
 * Converts validation errors to proper RFC 7807 format
 */
class HandleInvalidInputException
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            Log::info('HandleInvalidInputException middleware invoked', [
                'path' => $request->path(),
            ]);

            return $next($request);
        } catch (InvalidInputException $e) {
            Log::info('InvalidInputException caught in middleware', [
                'message' => $e->getMessage(),
            ]);
            // Return proper API error response for REST APIs
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'type'   => $e->getType(),
                    'title'  => $e->getTitle(),
                    'status' => $e->getStatus(),
                    'detail' => $e->getDetail(),
                ], $e->getStatusCode(), [], JSON_UNESCAPED_SLASHES);
            }

            throw $e;
        }
    }
}

<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pass-through middleware kept on api-platform routes for legacy reasons.
 *
 * Exception → JSON conversion is handled by ApiPlatformExceptionHandlerServiceProvider,
 * which wraps Laravel's exception handler and renders any exception implementing
 * HttpExceptionInterface + ProblemExceptionInterface as RFC 7807 JSON.
 */
class HandleInvalidInputException
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}

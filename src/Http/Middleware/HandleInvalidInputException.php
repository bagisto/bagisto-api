<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleInvalidInputException
{
    /**
     * Handle the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}

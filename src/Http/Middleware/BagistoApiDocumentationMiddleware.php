<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Http\Controllers\ApiEntrypointController;

/**
 * Serves the /api landing page, which API Platform's entrypoint route would otherwise answer.
 */
class BagistoApiDocumentationMiddleware
{
    public function __construct(protected Router $router) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getPathInfo() !== '/api') {
            return $next($request);
        }

        $route = $this->router->getRoutes()->getRoutesByMethod()['GET']['api'] ?? null;

        $result = $route
            ? $route->bind($request)->run()
            : app(ApiEntrypointController::class)($request);

        return Router::toResponse($request, $result)
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
}

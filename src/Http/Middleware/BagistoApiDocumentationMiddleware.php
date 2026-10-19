<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BagistoApiDocumentationMiddleware
{
    /**
     * Handle the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        if ($path === '/api') {
            return response()->view('webkul::api-platform.docs-index', [
                'documentation_url' => 'https://api-docs.bagisto.com',
                'graphql_playground_url' => config('app.url').'/api/graphql',

                'rest_apis' => [
                    [
                        'name' => 'Shop API',
                        'description' => 'Customer-facing API for shop operations, products, cart management, and orders',
                        'url' => '/api/shop',
                        'icon' => 'shop-api.svg',
                    ], [
                        'name' => 'Admin API',
                        'description' => 'Administrator API for store management, products, orders, and configurations',
                        'url' => '/api/admin',
                        'icon' => 'admin-api.svg',
                    ],
                ],
            ])->header('Content-Type', 'text/html; charset=UTF-8');
        }

        return $next($request);
    }
}

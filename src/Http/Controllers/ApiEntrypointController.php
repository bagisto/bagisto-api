<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Custom API entrypoint providing Bagisto-branded documentation index
 */
class ApiEntrypointController
{
    public function __invoke(Request $request, ?string $index = null, ?string $_format = null)
    {
        if ($_format && $_format !== '') {
            app()->make('api_platform.openapi.factory')->__invoke();
        }

        return view('webkul::api-platform.docs-index', [
            'documentation_url' => 'https://api-docs.bagisto.com',
            'rest_apis'         => [
                [
                    'name'        => 'Shop API',
                    'description' => 'Customer-facing API for shop operations, products, cart management, and orders',
                    'url'         => '/api/shop',
                    'icon'        => 'shop-api.svg',
                    'type'        => 'shop',
                ],
                [
                    'name'        => 'Admin API',
                    'description' => 'Administrator API for store management, products, orders, and configurations',
                    'url'         => '/api/admin',
                    'icon'        => 'admin-api.svg',
                    'type'        => 'admin',
                ],
            ],
            'graphql_url'            => '/graphql',
            'graphql_playground_url' => '/graphiql',
        ]);
    }
}

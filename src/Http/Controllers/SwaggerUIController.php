<?php

namespace Webkul\BagistoApi\Http\Controllers;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Webkul\BagistoApi\Services\OpenApiSerializer;

class SwaggerUIController extends Controller
{
    /**
     * Display Shop API Swagger UI
     * Shows all /api/shop routes and customer-facing endpoints.
     */
    public function shopApi(): View
    {
        $specData = $this->getOpenApiSpec('shop');

        return view('webkul::api-platform.swagger-ui-embedded', [
            'title' => 'Bagisto Shop API',
            'description' => 'Customer-facing API for shop operations',
            'specData' => $specData,
            'endpoint' => 'shop',
            'defaultServer' => '/api/shop',
        ]);
    }

    /**
     * Display Admin API Swagger UI
     * Shows all /api/admin routes and administrative endpoints.
     */
    public function adminApi(): View
    {
        $specData = $this->getOpenApiSpec('admin');

        return view('webkul::api-platform.swagger-ui-embedded', [
            'title' => 'Bagisto Admin API',
            'description' => 'Administrative API for platform management',
            'specData' => $specData,
            'endpoint' => 'admin',
            'defaultServer' => '/api/admin',
        ]);
    }

    /**
     * Get Shop API OpenAPI Specification (JSON)
     * Returns filtered OpenAPI spec for shop endpoints only.
     */
    public function shopApiDocs()
    {
        $specData = $this->getOpenApiSpec('shop');

        return response()->json($specData, 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get Admin API OpenAPI Specification (JSON)
     * Returns filtered OpenAPI spec for admin endpoints only.
     */
    public function adminApiDocs()
    {
        $specData = $this->getOpenApiSpec('admin');

        return response()->json($specData, 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Display API Documentation Index
     * Shows links to Shop and Admin API documentation.
     */
    public function index(): View
    {
        return view('webkul::api-platform.docs-index', [
            'apis' => [
                [
                    'name' => 'Shop API',
                    'description' => 'Customer-facing API for shop operations',
                    'url' => url('/api/shop'),
                    'icon' => '🛍️',
                ], [
                    'name' => 'Admin API',
                    'description' => 'Administrative API for platform management',
                    'url' => url('/api/admin'),
                    'icon' => '⚙️',
                ],
            ],
        ]);
    }

    /**
     * Retrieve OpenAPI specification array
     * Returns the spec as a PHP array (not JSON) for embedding in Swagger UI.
     */
    private function getOpenApiSpec(string $endpoint): array
    {
        try {
            $factory = app(OpenApiFactoryInterface::class);

            if (! $factory) {
                throw new \Exception('OpenAPI Factory could not be instantiated');
            }

            $context = [
                'endpoint' => $endpoint,
                'base_url' => request()->getBaseUrl(),
                'request' => request(),
            ];

            $openApi = $factory($context);

            $array = OpenApiSerializer::toArray($openApi);

            return $array ?: [];

        } catch (\Exception $exception) {
            \Log::error('OpenAPI Spec Generation Error: '.$exception->getMessage(), [
                'exception' => $exception,
                'endpoint' => $endpoint,
                'trace' => $exception->getTraceAsString(),
            ]);

            return [
                'openapi' => '3.0.0',

                'info' => [
                    'title' => 'Error',
                    'version' => '1.0.4',
                    'description' => 'Failed to generate OpenAPI specification: '.$exception->getMessage(),
                ],

                'paths' => [],

                'components' => [
                    'schemas' => [],
                ],
            ];
        }
    }
}

<?php

namespace Webkul\BagistoApi\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\View\View;

/**
 * Customizes Swagger UI for Shop and Admin APIs
 */
class SwaggerUIController extends Controller
{
    /**
     * Display Shop API Swagger UI
     * Shows all /api/shop routes and customer-facing endpoints
     * Embeds the OpenAPI spec directly to avoid self-referential requests
     */
    public function shopApi(): View
    {
        $specData = $this->getOpenApiSpec('shop');

        return view('webkul::api-platform.swagger-ui-embedded', [
            'title'         => 'Bagisto Shop API',
            'description'   => 'Customer-facing API for shop operations',
            'specData'      => $specData,
            'endpoint'      => 'shop',
            'defaultServer' => '/api/shop',
        ]);
    }

    /**
     * Display Admin API Swagger UI
     * Shows all /api/admin routes and administrative endpoints
     * Embeds the OpenAPI spec directly to avoid self-referential requests
     */
    public function adminApi(): View
    {
        $specData = $this->getOpenApiSpec('admin');

        return view('webkul::api-platform.swagger-ui-embedded', [
            'title'         => 'Bagisto Admin API',
            'description'   => 'Administrative API for platform management',
            'specData'      => $specData,
            'endpoint'      => 'admin',
            'defaultServer' => '/api/admin',
        ]);
    }

    /**
     * Get Shop API OpenAPI Specification (JSON)
     * Returns filtered OpenAPI spec for shop endpoints only
     */
    public function shopApiDocs()
    {
        $specData = $this->getOpenApiSpec('shop');

        return response()->json($specData, 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get Admin API OpenAPI Specification (JSON)
     * Returns filtered OpenAPI spec for admin endpoints only
     */
    public function adminApiDocs()
    {
        $specData = $this->getOpenApiSpec('admin');

        return response()->json($specData, 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Display API Documentation Index
     * Shows links to Shop and Admin API documentation
     */
    public function index(): View
    {
        return view('webkul::api-platform.docs-index', [
            'apis' => [
                [
                    'name'        => 'Shop API',
                    'description' => 'Customer-facing API for shop operations',
                    'url'         => url('/api/shop'),
                    'icon'        => '🛍️',
                ],
                [
                    'name'        => 'Admin API',
                    'description' => 'Administrative API for platform management',
                    'url'         => url('/api/admin'),
                    'icon'        => '⚙️',
                ],
            ],
        ]);
    }

    /**
     * Retrieve OpenAPI specification array
     * Returns the spec as a PHP array (not JSON) for embedding in Swagger UI
     */
    private function getOpenApiSpec(string $endpoint): array
    {
        try {
            // Get the already-registered OpenAPI Factory from the service container
            // It's already a SplitOpenApiFactory, so we just need to call it with context
            $factory = app(\ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface::class);

            if (! $factory) {
                throw new \Exception('OpenAPI Factory could not be instantiated');
            }

            // Create context with endpoint information
            // The SplitOpenApiFactory will use this to determine which endpoint to filter for
            $context = [
                'endpoint' => $endpoint,
                'base_url' => request()->getBaseUrl(),
                'request'  => request(),
            ];

            // Generate the OpenAPI spec using the factory
            // The SplitOpenApiFactory will handle filtering based on endpoint
            $openApi = $factory($context);

            // Use our custom serializer to properly convert the OpenAPI object to an array
            // This handles all the nested objects and complex types properly
            $array = \Webkul\BagistoApi\Services\OpenApiSerializer::toArray($openApi);

            return $array ?: [];

        } catch (\Exception $e) {
            \Log::error('OpenAPI Spec Generation Error: '.$e->getMessage(), [
                'exception' => $e,
                'endpoint'  => $endpoint,
                'trace'     => $e->getTraceAsString(),
            ]);

            return [
                'openapi' => '3.0.0',
                'info'    => [
                    'title'       => 'Error',
                    'version'     => '1.0.0',
                    'description' => 'Failed to generate OpenAPI specification: '.$e->getMessage(),
                ],
                'paths'      => [],
                'components' => [
                    'schemas' => [],
                ],
            ];
        }
    }
}

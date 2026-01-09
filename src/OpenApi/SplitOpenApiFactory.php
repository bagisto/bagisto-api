<?php

namespace Webkul\BagistoApi\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Components;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\OpenApi;

/**
 * Generates separate OpenAPI specs for Shop and Admin APIs
 */
class SplitOpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private OpenApiFactoryInterface $decorated) {}

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        // Detect which endpoint is being accessed
        // First check context (passed from controller), then fall back to REQUEST_URI detection
        $endpoint = $context['endpoint'] ?? $this->detectEndpoint();

        // Normalize endpoint to just 'shop' or 'admin' for comparison
        $endpointType = str_contains($endpoint, 'shop') ? 'shop' : 'admin';

        // Set appropriate server for this endpoint
        $servers = [
            new \ApiPlatform\OpenApi\Model\Server(
                url: '/api/'.$endpointType,
                description: $endpointType === 'shop' ? 'Shop API - Customer-facing endpoints' : 'Admin API - Administrative endpoints'
            ),
        ];

        $openApi = $this->withServers($openApi, $servers);

        // Filter paths based on endpoint
        if ($endpointType === 'shop') {
            $openApi = $this->filterShopPaths($openApi);
            $description = 'Bagisto Shop API - Customer-facing operations for products, cart, orders, and checkout.';
            // Add X-STOREFRONT-KEY security requirement for shop API
            $openApi = $this->addStorefrontKeyHeader($openApi);
        } else {
            $openApi = $this->filterAdminPaths($openApi);
            $description = 'Bagisto Admin API - Administrative operations for store management and configuration.';
        }

        // Update the main description
        $openApi = $this->withDescription($openApi, $description);

        // Filter tags and components to only include those used in the remaining paths
        $usedTags = [];
        $usedSchemas = [];

        // Extract used tags and schemas from paths
        foreach ($openApi->getPaths()->getPaths() as $pathItem) {
            $this->extractTags($pathItem, $usedTags);
            $this->extractSchemaReferences($pathItem, $usedSchemas);
        }

        // Filter tags based on what's used in paths
        $openApi = $this->filterTags($openApi, $usedTags);

        // Filter components based on what's used in paths
        if ($openApi->getComponents()) {
            $filteredComponents = $this->filterComponents($openApi->getComponents(), $usedSchemas);
            $openApi = $openApi->withComponents($filteredComponents);
        }

        return $openApi;
    }

    /**
     * Detect which endpoint is being accessed (/api/shop/docs vs /api/admin/docs)
     */
    private function detectEndpoint(): string
    {
        // Check REQUEST_URI for /docs routes
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';

        if (strpos($requestUri, '/api/shop') !== false) {
            return 'shop';
        } elseif (strpos($requestUri, '/api/admin') !== false) {
            return 'admin';
        }

        // Default to shop
        return 'api/shop';
    }

    /**
     * Filter to show only shop-related paths
     * Shop paths include:
     * - All /api/shop/* paths (explicitly shop)
     * - All generic paths (cart, checkout, customers, etc. - customer-facing)
     * - NO /api/admin/* paths
     */
    private function filterShopPaths(OpenApi $openApi): OpenApi
    {
        // Get all paths and filter
        $paths = $openApi->getPaths();
        $filteredPaths = new Paths;

        foreach ($paths->getPaths() as $path => $pathItem) {
            // Include /api/shop/* paths and all non-admin generic paths
            // Exclude only /api/admin/* paths
            if (strpos($path, '/api/admin') !== 0) {
                // Rewrite path to remove /api/shop prefix if present
                // This prevents duplicate /api/shop in URLs when server URL already contains it
                $normalizedPath = $this->normalizePath($path, 'shop');
                $filteredPaths->addPath($normalizedPath, $pathItem);
            }
        }

        return $openApi->withPaths($filteredPaths);
    }

    /**
     * Filter to show only admin-related paths
     * Admin paths are strictly /api/admin/* paths only
     */
    private function filterAdminPaths(OpenApi $openApi): OpenApi
    {
        // Get all paths and filter
        $paths = $openApi->getPaths();
        $filteredPaths = new Paths;

        foreach ($paths->getPaths() as $path => $pathItem) {
            // Include ONLY /api/admin/* paths
            // Exclude all shop and generic paths
            if (strpos($path, '/api/admin') === 0) {
                // Rewrite path to remove /api/admin prefix
                // This prevents duplicate /api/admin in URLs when server URL already contains it
                $normalizedPath = $this->normalizePath($path, 'admin');
                $filteredPaths->addPath($normalizedPath, $pathItem);
            }
        }

        return $openApi->withPaths($filteredPaths);
    }

    /**
     * Normalize path by removing the endpoint prefix
     * Converts /api/admin/path to /path or /api/shop/path to /path
     * For generic paths that don't have a prefix, returns them as-is
     */
    private function normalizePath(string $path, string $endpoint): string
    {
        $prefix = '/api/'.$endpoint.'/';

        if (strpos($path, $prefix) === 0) {
            // Remove the /api/{endpoint}/ prefix
            return '/'.substr($path, strlen($prefix));
        }

        // For generic paths without endpoint prefix, remove /api/ if present
        if (strpos($path, '/api/') === 0) {
            return substr($path, 4); // Remove '/api'
        }

        return $path;
    }

    /**
     * Add servers configuration to OpenAPI spec
     */
    private function withServers(OpenApi $openApi, array $servers): OpenApi
    {
        $reflectionClass = new \ReflectionClass($openApi);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor) {
            $params = [];
            foreach ($constructor->getParameters() as $param) {
                $paramName = $param->getName();
                if ($paramName === 'servers') {
                    $params[$paramName] = $servers;
                } else {
                    $property = $reflectionClass->getProperty($paramName);
                    $property->setAccessible(true);
                    $params[$paramName] = $property->getValue($openApi);
                }
            }

            return new OpenApi(...array_values($params));
        }

        return $openApi;
    }

    /**
     * Add description to OpenAPI spec
     */
    private function withDescription(OpenApi $openApi, string $description): OpenApi
    {
        $info = $openApi->getInfo();

        if ($info) {
            $reflectionClass = new \ReflectionClass($info);
            $constructor = $reflectionClass->getConstructor();

            if ($constructor) {
                $params = [];
                foreach ($constructor->getParameters() as $param) {
                    $paramName = $param->getName();
                    if ($paramName === 'description') {
                        $params[$paramName] = $description;
                    } else {
                        $property = $reflectionClass->getProperty($paramName);
                        $property->setAccessible(true);
                        $params[$paramName] = $property->getValue($info);
                    }
                }

                $newInfo = new \ApiPlatform\OpenApi\Model\Info(...array_values($params));

                return $openApi->withInfo($newInfo);
            }
        }

        return $openApi;
    }

    /**
     * Extract all tag references from a path item recursively
     */
    private function extractTags($item, &$usedTags): void
    {
        if ($item === null || is_scalar($item)) {
            return;
        }

        // Convert to array for easier traversal
        if ($item instanceof \ArrayObject) {
            $item = $item->getArrayCopy();
        } elseif (is_object($item)) {
            $item = (array) $item;
        }

        if (! is_array($item)) {
            return;
        }

        foreach ($item as $key => $value) {
            // Check if this is the tags array from an operation
            if ($key === 'tags' && is_array($value)) {
                foreach ($value as $tag) {
                    if (is_string($tag)) {
                        $usedTags[$tag] = true;
                    }
                }
            }

            // Recursively check nested structures
            if (is_array($value) || $value instanceof \ArrayObject) {
                $this->extractTags($value, $usedTags);
            } elseif (is_object($value)) {
                $this->extractTags($value, $usedTags);
            }
        }
    }

    /**
     * Filter OpenAPI tags to only include those that are actually used
     */
    private function filterTags(OpenApi $openApi, array $usedTags): OpenApi
    {
        $tags = $openApi->getTags();

        if (empty($tags)) {
            return $openApi;
        }

        $filteredTags = [];
        foreach ($tags as $tag) {
            if (isset($usedTags[$tag->getName()])) {
                $filteredTags[] = $tag;
            }
        }

        return $openApi->withTags($filteredTags);
    }

    /**
     * Extract all schema references from a path item recursively
     */
    private function extractSchemaReferences($item, &$usedSchemas): void
    {
        if ($item === null || is_scalar($item)) {
            return;
        }

        // Convert to array for easier traversal
        if ($item instanceof \ArrayObject) {
            $item = $item->getArrayCopy();
        } elseif (is_object($item)) {
            $item = (array) $item;
        }

        if (! is_array($item)) {
            return;
        }

        foreach ($item as $key => $value) {
            // Check if this value is a schema reference
            if ($key === '$ref' && is_string($value)) {
                if (preg_match('/#\/components\/schemas\/([a-zA-Z0-9._-]+)/', $value, $match)) {
                    $schemaName = $match[1];
                    $usedSchemas[$schemaName] = true;
                }
            }

            // Recursively check nested structures
            if (is_array($value) || $value instanceof \ArrayObject) {
                $this->extractSchemaReferences($value, $usedSchemas);
            } elseif (is_object($value)) {
                $this->extractSchemaReferences($value, $usedSchemas);
            }
        }
    }

    /**
     * Filter components to only include schemas that are actually used
     */
    private function filterComponents($components, array $usedSchemas): ?Components
    {
        if (! $components) {
            return $components;
        }

        $schemas = $components->getSchemas() ?? [];
        $filteredSchemas = [];

        // Keep iterating until no new schemas are discovered (for nested references)
        $previousCount = 0;
        while (count($usedSchemas) > $previousCount) {
            $previousCount = count($usedSchemas);

            foreach ($usedSchemas as $schemaName => $used) {
                if (! isset($filteredSchemas[$schemaName]) && isset($schemas[$schemaName])) {
                    $filteredSchemas[$schemaName] = $schemas[$schemaName];
                    // Check for nested schema references
                    $this->extractSchemaReferences($schemas[$schemaName], $usedSchemas);
                }
            }
        }

        // Create new Components with filtered schemas
        $reflectionClass = new \ReflectionClass($components);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor) {
            $params = [];
            foreach ($constructor->getParameters() as $param) {
                $paramName = $param->getName();
                if ($paramName === 'schemas') {
                    // Convert array to ArrayObject
                    $params[$paramName] = new \ArrayObject($filteredSchemas);
                } else {
                    // Get original value using reflection
                    $property = $reflectionClass->getProperty($paramName);
                    $property->setAccessible(true);
                    $params[$paramName] = $property->getValue($components);
                }
            }

            return new Components(...array_values($params));
        }

        return $components;
    }

    /**
     * Add X-STOREFRONT-KEY header requirement to all shop API operations
     * This header is required for authenticating shop/storefront API requests
     */
    private function addStorefrontKeyHeader(OpenApi $openApi): OpenApi
    {
        $paths = $openApi->getPaths();
        $modifiedPaths = new Paths;

        foreach ($paths->getPaths() as $path => $pathItem) {
            // Get all operations from the path item
            $pathItem = $this->addHeaderToPathItem($pathItem);
            $modifiedPaths->addPath($path, $pathItem);
        }

        return $openApi->withPaths($modifiedPaths);
    }

    /**
     * Add X-STOREFRONT-KEY header parameter to all operations in a path item
     */
    private function addHeaderToPathItem($pathItem)
    {
        if (! is_object($pathItem)) {
            return $pathItem;
        }

        // Get the class to check what methods are available
        $reflectionClass = new \ReflectionClass($pathItem);

        // List of HTTP method getters
        $methods = ['getGet', 'getPost', 'getPut', 'getPatch', 'getDelete', 'getHead', 'getOptions', 'getTrace'];

        foreach ($methods as $methodName) {
            if (method_exists($pathItem, $methodName)) {
                $operation = $pathItem->$methodName();

                if ($operation && is_object($operation)) {
                    // Add the header to this operation
                    $operation = $this->addHeaderToOperation($operation);

                    // Set the operation back on the path item
                    $setterName = 'with'.substr($methodName, 3); // getGet -> withGet
                    if (method_exists($pathItem, $setterName)) {
                        $pathItem = $pathItem->$setterName($operation);
                    }
                }
            }
        }

        return $pathItem;
    }

    /**
     * Add X-STOREFRONT-KEY header parameter to an operation
     */
    private function addHeaderToOperation($operation)
    {
        if (! is_object($operation)) {
            return $operation;
        }

        // Get existing parameters
        $parameters = [];
        if (method_exists($operation, 'getParameters')) {
            $existingParams = $operation->getParameters();
            if ($existingParams) {
                $parameters = is_array($existingParams) ? $existingParams : iterator_to_array($existingParams);
            }
        }

        // Check if X-STOREFRONT-KEY header already exists
        $headerExists = false;
        foreach ($parameters as $param) {
            if (is_object($param) && method_exists($param, 'getName') && $param->getName() === 'X-STOREFRONT-KEY') {
                $headerExists = true;
                break;
            }
        }

        // If header doesn't exist, add it
        if (! $headerExists) {
            // Only include the example key if auto-inject is enabled for security
            $playgroundKey = env('API_PLAYGROUND_AUTO_INJECT_STOREFRONT_KEY', false) ? (env('STOREFRONT_PLAYGROUND_KEY') ?? 'pk_storefront_xxxxx') : '';

            // Create the X-STOREFRONT-KEY header parameter
            $headerParam = new \ApiPlatform\OpenApi\Model\Parameter(
                name: 'X-STOREFRONT-KEY',
                in: 'header',
                description: 'Storefront API Key for authentication. Required for all shop/storefront API requests.',
                required: true,
                deprecated: false,
                allowEmptyValue: false,
                schema: [
                    'type'    => 'string',
                    'example' => $playgroundKey ?? '',
                ]
            );

            $parameters[] = $headerParam;

            // Set parameters back to operation
            if (method_exists($operation, 'withParameters')) {
                $operation = $operation->withParameters($parameters);
            }
        }

        return $operation;
    }
}

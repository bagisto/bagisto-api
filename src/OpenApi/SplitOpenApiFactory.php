<?php

namespace Webkul\BagistoApi\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Components;
use ApiPlatform\OpenApi\Model\Info;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\Model\Server;
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

        // Inject Laravel-backed shop routes that aren't registered as ApiResource operations
        // (they return binary file responses, so they live as plain Laravel controllers — but
        // we still want them documented in Swagger).
        if ($endpointType === 'shop') {
            $openApi = $this->addLaravelBackedShopPaths($openApi);
        }

        // Set appropriate server for this endpoint
        $servers = [
            new Server(
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

        $openApi = $this->cleanPathParameters($openApi);

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
     * Drop path parameters the route does not carry and replace auto-generated
     * "<Resource> identifier" descriptions with consumer-facing wording.
     */
    private function cleanPathParameters(OpenApi $openApi): OpenApi
    {
        $methods = ['get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'trace'];

        $paths = new Paths;

        foreach ($openApi->getPaths()->getPaths() as $path => $pathItem) {
            foreach ($methods as $method) {
                $operation = $pathItem->{'get'.ucfirst($method)}();

                if (! $operation) {
                    continue;
                }

                $original = $operation->getParameters();
                $parameters = [];

                foreach ($original as $parameter) {
                    if (
                        $parameter->getIn() === 'path'
                        && ! str_contains($path, '{'.$parameter->getName().'}')
                    ) {
                        continue;
                    }

                    $parameters[] = $this->withReadableParameterDescription($parameter);
                }

                if ($parameters == $original) {
                    continue;
                }

                $pathItem = $pathItem->{'with'.ucfirst($method)}($operation->withParameters($parameters));
            }

            $paths->addPath($path, $pathItem);
        }

        return $openApi->withPaths($paths);
    }

    /**
     * Rewrite "AdminOrderComment identifier" into "Order comment ID".
     */
    private function withReadableParameterDescription(Parameter $parameter): Parameter
    {
        if (! preg_match('/^([A-Za-z]+) identifier$/', (string) $parameter->getDescription(), $matches)) {
            return $parameter;
        }

        $words = preg_split('/(?=[A-Z])/', preg_replace('/^Admin/', '', $matches[1]), -1, PREG_SPLIT_NO_EMPTY);

        if (! $words) {
            return $parameter;
        }

        $words = array_map('strtolower', $words);
        $words[0] = ucfirst($words[0]);

        return $parameter->withDescription(implode(' ', $words).' ID');
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

                $newInfo = new Info(...array_values($params));

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

        // Collect header names that already exist
        $existingHeaders = [];
        foreach ($parameters as $param) {
            if (is_object($param) && method_exists($param, 'getName')) {
                $existingHeaders[] = $param->getName();
            }
        }

        // Only include the example key if auto-inject is enabled for security
        $playgroundKey = config('storefront.auto_inject_playground_key') ? (config('storefront.playground_key') ?: 'pk_storefront_xxxxx') : '';

        // Define all headers that should be present on every operation
        $headersToAdd = [
            [
                'name' => 'X-STOREFRONT-KEY',
                'description' => 'Storefront API Key for authentication. Required for all shop/storefront API requests.',
                'required' => true,
                'schema' => ['type' => 'string', 'example' => $playgroundKey ?? ''],
            ],
            [
                'name' => 'X-Locale',
                'description' => 'Locale code for localized data (e.g. "en", "fr", "ar"). Defaults to channel\'s default locale.',
                'required' => false,
                'schema' => ['type' => 'string', 'example' => 'en'],
            ],
            [
                'name' => 'X-Channel',
                'description' => 'Channel code (e.g. "default"). Defaults to the current channel.',
                'required' => false,
                'schema' => ['type' => 'string', 'example' => 'default'],
            ],
            [
                'name' => 'X-Currency',
                'description' => 'Currency code (e.g. "USD", "EUR", "INR"). Defaults to channel\'s base currency.',
                'required' => false,
                'schema' => ['type' => 'string', 'example' => 'USD'],
            ],
        ];

        foreach ($headersToAdd as $header) {
            if (! in_array($header['name'], $existingHeaders)) {
                $parameters[] = new Parameter(
                    name: $header['name'],
                    in: 'header',
                    description: $header['description'],
                    required: $header['required'],
                    deprecated: false,
                    allowEmptyValue: false,
                    schema: $header['schema']
                );
            }
        }

        // Set parameters back to operation
        if (method_exists($operation, 'withParameters')) {
            $operation = $operation->withParameters($parameters);
        }

        return $operation;
    }

    /**
     * Inject paths for Laravel-backed routes that aren't declared as ApiResource
     * operations (e.g. binary-file endpoints). These paths are picked up by the
     * tag-filter and storefront-header logic later in the pipeline.
     */
    private function addLaravelBackedShopPaths(OpenApi $openApi): OpenApi
    {
        $paths = $openApi->getPaths();

        $bearerParam = new Parameter(
            name: 'Authorization',
            in: 'header',
            description: 'Bearer token for authenticated customer (format: `Bearer <token>`).',
            required: true,
            deprecated: false,
            allowEmptyValue: false,
            schema: ['type' => 'string', 'example' => 'Bearer 1234|abcdef...'],
        );
        $idParam = new Parameter(
            name: 'id',
            in: 'path',
            description: 'Identifier',
            required: true,
            deprecated: false,
            allowEmptyValue: false,
            schema: ['type' => 'integer'],
        );

        $binaryResponse = new Response(
            description: 'Binary file stream',
            content: new \ArrayObject([
                'application/octet-stream' => ['schema' => ['type' => 'string', 'format' => 'binary']],
            ]),
        );

        $pdfOp = new Operation(
            operationId: 'downloadCustomerInvoicePdf',
            tags: ['Customer Order'],
            responses: [
                '200' => $binaryResponse,
                '401' => new Response(description: 'Unauthorized'),
                '403' => new Response(description: 'Forbidden'),
                '404' => new Response(description: 'Invoice not found'),
            ],
            summary: 'Download invoice PDF',
            description: 'Streams the PDF for a customer invoice. Requires Bearer token; the invoice must belong to the authenticated customer.',
            parameters: [$idParam, $bearerParam],
        );

        $downloadOp = new Operation(
            operationId: 'downloadCustomerDownloadableProduct',
            tags: ['Customer Order'],
            responses: [
                '200' => $binaryResponse,
                '401' => new Response(description: 'Unauthorized'),
                '403' => new Response(description: 'Forbidden (pending / download limit exceeded)'),
                '404' => new Response(description: 'Download not found'),
            ],
            summary: 'Download purchased downloadable product',
            description: 'Streams the purchased file. Increments `download_used` on each successful call. `{id}` is the `downloadable_link_purchased` row id from `GET /customer-downloadable-products`.',
            parameters: [$idParam, $bearerParam],
        );

        $paths->addPath(
            '/api/shop/customer-invoices/{id}/pdf',
            (new PathItem)->withGet($pdfOp),
        );
        $paths->addPath(
            '/api/shop/customer-downloadable-products/{id}/download',
            (new PathItem)->withGet($downloadOp),
        );

        return $openApi->withPaths($paths);
    }
}

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
use ApiPlatform\OpenApi\Model\Tag;
use ApiPlatform\OpenApi\OpenApi;

/**
 * Generates separate OpenAPI specs for Shop and Admin APIs
 */
class SplitOpenApiFactory implements OpenApiFactoryInterface
{
    private const HTTP_METHODS = ['get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'trace'];

    public function __construct(private OpenApiFactoryInterface $decorated) {}

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        $endpoint = $context['endpoint'] ?? $this->detectEndpoint();

        $endpointType = str_contains($endpoint, 'shop') ? 'shop' : 'admin';

        if ($endpointType === 'shop') {
            $openApi = $this->addLaravelBackedShopPaths($openApi);
        }

        $servers = [
            new Server(
                url: '/api/'.$endpointType,
                description: $endpointType === 'shop' ? 'Shop API - Customer-facing endpoints' : 'Admin API - Administrative endpoints'
            ),
        ];

        $openApi = $this->withServers($openApi, $servers);

        if ($endpointType === 'shop') {
            $openApi = $this->filterShopPaths($openApi);
            $description = 'Bagisto Shop API - Customer-facing operations for products, cart, orders, and checkout.';
            $openApi = $this->addStorefrontKeyHeader($openApi);
        } else {
            $openApi = $this->filterAdminPaths($openApi);
            $description = 'Bagisto Admin API - Administrative operations for store management and configuration.';
        }

        $openApi = $this->cleanPathParameters($openApi);

        $openApi = $this->withDescription($openApi, $description);

        $usedTags = [];
        $usedSchemas = [];

        foreach ($openApi->getPaths()->getPaths() as $pathItem) {
            $this->extractTags($pathItem, $usedTags);
            $this->extractSchemaReferences($pathItem, $usedSchemas);
        }

        $openApi = $this->filterTags($openApi, $usedTags);

        if ($openApi->getComponents()) {
            $filteredComponents = $this->filterComponents($openApi->getComponents(), $usedSchemas);
            $openApi = $openApi->withComponents($filteredComponents);
        }

        return $openApi;
    }

    private function cleanPathParameters(OpenApi $openApi): OpenApi
    {
        $paths = new Paths;

        foreach ($openApi->getPaths()->getPaths() as $path => $pathItem) {
            foreach (self::HTTP_METHODS as $method) {
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
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';

        if (strpos($requestUri, '/api/shop') !== false) {
            return 'shop';
        } elseif (strpos($requestUri, '/api/admin') !== false) {
            return 'admin';
        }

        return 'api/shop';
    }

    private function filterShopPaths(OpenApi $openApi): OpenApi
    {
        $paths = $openApi->getPaths();
        $filteredPaths = new Paths;

        foreach ($paths->getPaths() as $path => $pathItem) {
            if (strpos($path, '/api/admin') !== 0) {
                $normalizedPath = $this->normalizePath($path, 'shop');
                $filteredPaths->addPath($normalizedPath, $pathItem);
            }
        }

        return $openApi->withPaths($filteredPaths);
    }

    private function filterAdminPaths(OpenApi $openApi): OpenApi
    {
        $paths = $openApi->getPaths();
        $filteredPaths = new Paths;

        foreach ($paths->getPaths() as $path => $pathItem) {
            if (strpos($path, '/api/admin') === 0) {
                $normalizedPath = $this->normalizePath($path, 'admin');
                $filteredPaths->addPath($normalizedPath, $pathItem);
            }
        }

        return $openApi->withPaths($filteredPaths);
    }

    private function normalizePath(string $path, string $endpoint): string
    {
        $prefix = '/api/'.$endpoint.'/';

        if (strpos($path, $prefix) === 0) {
            return '/'.substr($path, strlen($prefix));
        }

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
     * Collect the tag names used by a path item's operations.
     *
     * @param  array<string, true>  $usedTags
     */
    private function extractTags(PathItem $pathItem, array &$usedTags): void
    {
        foreach (self::HTTP_METHODS as $method) {
            $operation = $pathItem->{'get'.ucfirst($method)}();

            if (! $operation) {
                continue;
            }

            foreach ($operation->getTags() ?? [] as $tag) {
                if (is_string($tag) && $tag !== '') {
                    $usedTags[$tag] = true;
                }
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
                // Drop the tag description. It is auto-derived from a resource
                // class docblock, which carries internal implementation notes
                // (e.g. "bypass API Platform's ID requirement") not meant for
                // consumers. The tag name alone labels the group.
                $filteredTags[] = new Tag($tag->getName());
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

        if ($item instanceof \ArrayObject) {
            $item = $item->getArrayCopy();
        } elseif (is_object($item)) {
            $item = (array) $item;
        }

        if (! is_array($item)) {
            return;
        }

        foreach ($item as $key => $value) {
            if ($key === '$ref' && is_string($value)) {
                if (preg_match('/#\/components\/schemas\/([a-zA-Z0-9._-]+)/', $value, $match)) {
                    $schemaName = $match[1];
                    $usedSchemas[$schemaName] = true;
                }
            }

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

        $previousCount = 0;
        while (count($usedSchemas) > $previousCount) {
            $previousCount = count($usedSchemas);

            foreach ($usedSchemas as $schemaName => $used) {
                if (! isset($filteredSchemas[$schemaName]) && isset($schemas[$schemaName])) {
                    $filteredSchemas[$schemaName] = $schemas[$schemaName];
                    $this->extractSchemaReferences($schemas[$schemaName], $usedSchemas);
                }
            }
        }

        $reflectionClass = new \ReflectionClass($components);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor) {
            $params = [];
            foreach ($constructor->getParameters() as $param) {
                $paramName = $param->getName();
                if ($paramName === 'schemas') {
                    $params[$paramName] = new \ArrayObject($filteredSchemas);
                } else {
                    $property = $reflectionClass->getProperty($paramName);
                    $property->setAccessible(true);
                    $params[$paramName] = $property->getValue($components);
                }
            }

            return new Components(...array_values($params));
        }

        return $components;
    }

    private function addStorefrontKeyHeader(OpenApi $openApi): OpenApi
    {
        $paths = $openApi->getPaths();
        $modifiedPaths = new Paths;

        foreach ($paths->getPaths() as $path => $pathItem) {
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

        $reflectionClass = new \ReflectionClass($pathItem);

        $methods = ['getGet', 'getPost', 'getPut', 'getPatch', 'getDelete', 'getHead', 'getOptions', 'getTrace'];

        foreach ($methods as $methodName) {
            if (method_exists($pathItem, $methodName)) {
                $operation = $pathItem->$methodName();

                if ($operation && is_object($operation)) {
                    $operation = $this->addHeaderToOperation($operation);

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

        $parameters = [];
        if (method_exists($operation, 'getParameters')) {
            $existingParams = $operation->getParameters();
            if ($existingParams) {
                $parameters = is_array($existingParams) ? $existingParams : iterator_to_array($existingParams);
            }
        }

        $headerExists = false;
        foreach ($parameters as $param) {
            if (is_object($param) && method_exists($param, 'getName') && $param->getName() === 'X-STOREFRONT-KEY') {
                $headerExists = true;
                break;
            }
        }

        $existingHeaders = [];
        foreach ($parameters as $param) {
            if (is_object($param) && method_exists($param, 'getName')) {
                $existingHeaders[] = $param->getName();
            }
        }

        $playgroundKey = config('storefront.auto_inject_playground_key') ? (config('storefront.playground_key') ?: 'pk_storefront_xxxxx') : '';

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

        if (method_exists($operation, 'withParameters')) {
            $operation = $operation->withParameters($parameters);
        }

        return $operation;
    }

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

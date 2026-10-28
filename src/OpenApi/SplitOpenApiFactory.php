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

class SplitOpenApiFactory implements OpenApiFactoryInterface
{
    /**
     * The http methods constant.
     */
    private const HTTP_METHODS = ['get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'trace'];

    /**
     * The messages the purchased download route refuses a request with, by error code.
     */
    private const DOWNLOAD_REFUSALS = [
        'unauthenticated' => 'Unauthorized: Customer authentication required.',
        'not_found' => 'Downloadable product not found.',
        'download_pending' => 'Download is pending. Please wait for the order to be invoiced.',
        'download_limit_exceeded' => 'Download limit exceeded.',
        'no_downloads_remaining' => 'No more downloads available.',
        'file_not_found' => 'File not found.',
    ];

    /**
     * The schema of a purchased download refusal.
     */
    private const DOWNLOAD_REFUSAL_SCHEMA = [
        'type' => 'object',
        'required' => ['message', 'error'],

        'properties' => [
            'message' => ['type' => 'string'],

            'error' => [
                'type' => 'string',
                'enum' => ['unauthenticated', 'not_found', 'download_pending', 'download_limit_exceeded', 'no_downloads_remaining', 'file_not_found'],
            ],
        ],
    ];

    /**
     * Create a new instance.
     */
    public function __construct(private OpenApiFactoryInterface $decorated) {}

    /**
     * Handle the invoke magic method.
     */
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
            $description = ApiGuide::SHOP;
            $openApi = $this->addStorefrontKeyHeader($openApi);
        } else {
            $openApi = $this->filterAdminPaths($openApi);
            $description = ApiGuide::ADMIN;
        }

        $openApi = $this->cleanPathParameters($openApi);

        $openApi = $this->withDescription($openApi, $description);

        $openApi = (new AccessDocumenter)($openApi, $endpointType);

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

    /**
     * Clean path parameters.
     */
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
                $parameters = $this->cleanParameters($path, $original);

                if ($parameters != $original) {
                    $pathItem = $pathItem->{'with'.ucfirst($method)}($operation->withParameters($parameters));
                }
            }

            $paths->addPath($path, $pathItem);
        }

        return $openApi->withPaths($paths);
    }

    /**
     * Drop path parameters the path does not use and make the rest readable.
     */
    private function cleanParameters(string $path, array $parameters): array
    {
        $cleaned = [];

        foreach ($parameters as $parameter) {
            if (
                $parameter->getIn() === 'path'
                && ! str_contains($path, '{'.$parameter->getName().'}')
            ) {
                continue;
            }

            $cleaned[] = $this->withReadableParameterDescription($parameter);
        }

        return $cleaned;
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
     * Detect which endpoint is being accessed (/api/shop/docs vs /api/admin/docs).
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

    /**
     * Filter shop paths.
     */
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

    /**
     * Filter admin paths.
     */
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

    /**
     * Normalize path.
     */
    private function normalizePath(string $path, string $endpoint): string
    {
        $prefix = '/api/'.$endpoint.'/';

        if (strpos($path, $prefix) === 0) {
            return '/'.substr($path, strlen($prefix));
        }

        if (strpos($path, '/api/') === 0) {
            return substr($path, 4);
        }

        return $path;
    }

    /**
     * Add servers configuration to OpenAPI spec.
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
     * Add description to OpenAPI spec.
     */
    private function withDescription(OpenApi $openApi, string $description): OpenApi
    {
        $info = $openApi->getInfo();

        if (! $info) {
            return $openApi;
        }

        $reflectionClass = new \ReflectionClass($info);
        $constructor = $reflectionClass->getConstructor();

        if (! $constructor) {
            return $openApi;
        }

        $params = [];

        foreach ($constructor->getParameters() as $param) {
            $paramName = $param->getName();

            if ($paramName === 'description') {
                $params[$paramName] = $description;

                continue;
            }

            $property = $reflectionClass->getProperty($paramName);
            $property->setAccessible(true);
            $params[$paramName] = $property->getValue($info);
        }

        return $openApi->withInfo(new Info(...array_values($params)));
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
                if (
                    is_string($tag)
                    && $tag !== ''
                ) {
                    $usedTags[$tag] = true;
                }
            }
        }
    }

    /**
     * Filter OpenAPI tags to only include those that are actually used.
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
                $filteredTags[] = new Tag($tag->getName());
            }
        }

        return $openApi->withTags($filteredTags);
    }

    /**
     * Extract all schema references from a path item recursively.
     */
    private function extractSchemaReferences($item, &$usedSchemas): void
    {
        if (
            $item === null
            || is_scalar($item)
        ) {
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
            if (
                $key === '$ref'
                && is_string($value)
            ) {
                if (preg_match('/#\/components\/schemas\/([a-zA-Z0-9._-]+)/', $value, $match)) {
                    $schemaName = $match[1];
                    $usedSchemas[$schemaName] = true;
                }
            }

            if (
                is_array($value)
                || $value instanceof \ArrayObject
            ) {
                $this->extractSchemaReferences($value, $usedSchemas);
            } elseif (is_object($value)) {
                $this->extractSchemaReferences($value, $usedSchemas);
            }
        }
    }

    /**
     * Filter components to only include schemas that are actually used.
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
                if (
                    ! isset($filteredSchemas[$schemaName])
                    && isset($schemas[$schemaName])
                ) {
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

    /**
     * Add storefront key header.
     */
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
     * Add X-STOREFRONT-KEY header parameter to all operations in a path item.
     */
    private function addHeaderToPathItem($pathItem)
    {
        if (! is_object($pathItem)) {
            return $pathItem;
        }

        $methods = ['getGet', 'getPost', 'getPut', 'getPatch', 'getDelete', 'getHead', 'getOptions', 'getTrace'];

        foreach ($methods as $methodName) {
            $setterName = 'with'.substr($methodName, 3);

            if (
                ! method_exists($pathItem, $methodName)
                || ! method_exists($pathItem, $setterName)
            ) {
                continue;
            }

            $operation = $pathItem->$methodName();

            if (
                $operation
                && is_object($operation)
            ) {
                $pathItem = $pathItem->$setterName($this->addHeaderToOperation($operation));
            }
        }

        return $pathItem;
    }

    /**
     * Add X-STOREFRONT-KEY header parameter to an operation.
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
            if (
                is_object($param)
                && method_exists($param, 'getName')
                && $param->getName() === 'X-STOREFRONT-KEY'
            ) {
                $headerExists = true;
                break;
            }
        }

        $existingHeaders = [];
        foreach ($parameters as $param) {
            if (
                is_object($param)
                && method_exists($param, 'getName')
            ) {
                $existingHeaders[] = $param->getName();
            }
        }

        $playgroundKey = config('storefront.auto_inject_playground_key') ? (config('storefront.playground_key') ?: 'pk_storefront_xxxxx') : '';

        $headersToAdd = [
            [
                'name' => 'X-STOREFRONT-KEY',
                'description' => 'Storefront API Key for authentication. Required for all shop/storefront API requests.',
                'required' => true,

                'schema' => [
                    'type' => 'string',
                    'example' => $playgroundKey ?? '',
                ],
            ], [
                'name' => 'X-Locale',
                'description' => 'Locale code for localized data (e.g. "en", "fr", "ar"). Defaults to channel\'s default locale.',
                'required' => false,

                'schema' => [
                    'type' => 'string',
                    'example' => 'en',
                ],
            ], [
                'name' => 'X-Channel',
                'description' => 'Channel code (e.g. "default"). Defaults to the current channel.',
                'required' => false,

                'schema' => [
                    'type' => 'string',
                    'example' => 'default',
                ],
            ], [
                'name' => 'X-Currency',
                'description' => 'Currency code (e.g. "USD", "EUR", "INR"). Defaults to channel\'s base currency.',
                'required' => false,

                'schema' => [
                    'type' => 'string',
                    'example' => 'USD',
                ],
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

    /**
     * Add the storefront downloads served by plain Laravel routes rather than API Platform operations.
     */
    private function addLaravelBackedShopPaths(OpenApi $openApi): OpenApi
    {
        $paths = $openApi->getPaths();

        $idParam = new Parameter(
            name: 'id',
            in: 'path',
            description: 'Identifier',
            required: true,
            deprecated: false,
            allowEmptyValue: false,
            schema: ['type' => 'integer'],
        );

        $missingKey = $this->plainErrorResponse(
            'The `X-STOREFRONT-KEY` header is missing (`missing_key`).',
            ApiSchemas::STOREFRONT_KEY_ERROR_REF,
            ['missing_key' => [
                'message' => 'X-STOREFRONT-KEY header is required',
                'error' => 'missing_key',
                'header_name' => 'X-STOREFRONT-KEY',
                'key_type' => 'shop',
            ]],
        );

        $invalidKey = [
            'message' => 'Invalid or inactive shop API key',
            'error' => 'invalid_key',
        ];

        $noCustomer = ApiSchemas::problem(403, 'Forbidden', 'Unauthenticated. Please login to perform this action');

        $pdfOp = new Operation(
            operationId: 'downloadCustomerInvoicePdf',
            tags: ['Customer Order'],
            responses: [
                '200' => $this->fileResponse('The invoice PDF, as an attachment named `invoice-<dd-mm-yyyy>.pdf`.', 'application/pdf'),
                '401' => $missingKey,
                '403' => $this->plainErrorResponse(
                    'The storefront key is invalid (`invalid_key`), or no customer token was sent (a problem document).',
                    [ApiSchemas::STOREFRONT_KEY_ERROR_REF, ApiSchemas::PROBLEM_REF],
                    [
                        'invalid_key' => $invalidKey,
                        'no_customer_token' => $noCustomer,
                    ],
                ),
                '404' => $this->plainErrorResponse(
                    'No invoice of the customer\'s orders has this id.',
                    ApiSchemas::PROBLEM_REF,
                    ['not_found' => ApiSchemas::problem(404, 'Not Found', 'Customer invoice with ID "7" not found')],
                ),
            ],
            summary: 'Download invoice PDF',
            description: 'Streams the PDF of one of the customer\'s invoices, rendered by core\'s storefront invoice view. Needs the customer token; an invoice of another customer answers 404.',
            parameters: [$idParam],
        );

        $downloadOp = new Operation(
            operationId: 'downloadCustomerDownloadableProduct',
            tags: ['Customer Order'],
            responses: [
                '200' => $this->fileResponse('The purchased file, as an attachment, with its own content type.', 'application/octet-stream'),
                '401' => $this->plainErrorResponse(
                    'The `X-STOREFRONT-KEY` header is missing (`missing_key`), or no customer token was sent (`unauthenticated`).',
                    [ApiSchemas::STOREFRONT_KEY_ERROR_REF, self::DOWNLOAD_REFUSAL_SCHEMA],
                    [
                        'missing_key' => [
                            'message' => 'X-STOREFRONT-KEY header is required',
                            'error' => 'missing_key',
                            'header_name' => 'X-STOREFRONT-KEY',
                            'key_type' => 'shop',
                        ],
                        'unauthenticated' => $this->downloadRefusal('unauthenticated'),
                    ],
                ),
                '403' => $this->plainErrorResponse(
                    'The storefront key is invalid (`invalid_key`), the purchase is still pending (`download_pending`), or no downloads remain (`download_limit_exceeded`, `no_downloads_remaining`).',
                    [ApiSchemas::STOREFRONT_KEY_ERROR_REF, self::DOWNLOAD_REFUSAL_SCHEMA],
                    [
                        'invalid_key' => $invalidKey,
                        'download_pending' => $this->downloadRefusal('download_pending'),
                        'download_limit_exceeded' => $this->downloadRefusal('download_limit_exceeded'),
                        'no_downloads_remaining' => $this->downloadRefusal('no_downloads_remaining'),
                    ],
                ),
                '404' => $this->plainErrorResponse(
                    'No purchase of the customer has this id, or its link is invalid (`not_found`), or its file is missing (`file_not_found`).',
                    self::DOWNLOAD_REFUSAL_SCHEMA,
                    [
                        'not_found' => $this->downloadRefusal('not_found'),
                        'file_not_found' => $this->downloadRefusal('file_not_found'),
                    ],
                ),
            ],
            summary: 'Download purchased downloadable product',
            description: 'Streams the purchased file. Increments `download_used` on each successful call. `{id}` is the `downloadable_link_purchased` row id from `GET /customer-downloadable-products`. Needs the customer token. Errors carry a code in `error`.',
            parameters: [$idParam],
        );

        $gdprPdfOp = new Operation(
            operationId: 'downloadGdprDataPdf',
            tags: ['GDPR'],
            responses: [
                '200' => $this->fileResponse('The customer\'s data as a PDF attachment named `customerInfo.pdf`.', 'application/pdf'),
                '401' => new Response(description: 'The `X-STOREFRONT-KEY` header is missing (`missing_key`), or no customer token was sent.'),
                '403' => new Response(description: 'The storefront key is invalid or inactive (`invalid_key`).'),
                '404' => new Response(description: 'GDPR is disabled.'),
            ],
            summary: 'Download GDPR data PDF',
            description: "Streams the authenticated customer's GDPR data export (profile, addresses and orders) as a PDF rendered by core's GDPR view. Needs the customer token and GDPR enabled.",
        );

        $attachmentOp = new Operation(
            operationId: 'downloadCustomerReturnMessageAttachment',
            tags: ['Customer Return'],
            responses: [
                '200' => $this->fileResponse('The file attached to the message, as an attachment under its original name and with its own content type.', 'application/octet-stream'),
                '401' => $missingKey,
                '403' => $this->plainErrorResponse(
                    'The storefront key is invalid (`invalid_key`), or no customer token was sent (a problem document).',
                    [ApiSchemas::STOREFRONT_KEY_ERROR_REF, ApiSchemas::PROBLEM_REF],
                    [
                        'invalid_key' => $invalidKey,
                        'no_customer_token' => $noCustomer,
                    ],
                ),
                '404' => $this->plainErrorResponse(
                    'No message on the customer\'s own returns has this id, the message has no attachment, or its file is missing.',
                    ApiSchemas::PROBLEM_REF,
                    ['not_found' => ApiSchemas::problem(404, 'Not Found', 'Message attachment not found.')],
                ),
            ],
            summary: 'Download a return message attachment',
            description: 'Streams the file attached to a message on one of the customer\'s returns, as core\'s storefront return page does. `{id}` is the message id from `GET /return-messages`. Needs the customer token; a message on another customer\'s return answers 404.',
            parameters: [$idParam],
        );

        $paths->addPath(
            '/api/shop/customer-invoices/{id}/pdf',
            (new PathItem)->withGet($pdfOp),
        );
        $paths->addPath(
            '/api/shop/customer-downloadable-products/{id}/download',
            (new PathItem)->withGet($downloadOp),
        );
        $paths->addPath(
            '/api/shop/gdpr-data/pdf',
            (new PathItem)->withGet($gdprPdfOp),
        );
        $paths->addPath(
            '/api/shop/return-messages/{id}/attachment',
            (new PathItem)->withGet($attachmentOp),
        );

        return $openApi->withPaths($paths);
    }

    /**
     * A successful file download response of a media type.
     */
    private function fileResponse(string $description, string $mediaType): Response
    {
        return new Response(
            description: $description,
            content: new \ArrayObject([
                $mediaType => ['schema' => [
                    'type' => 'string',
                    'format' => 'binary',
                ]],
            ]),
            headers: new \ArrayObject([
                'Content-Disposition' => [
                    'description' => '`attachment` with the file name.',
                    'schema' => ['type' => 'string'],
                ],
            ]),
        );
    }

    /**
     * A JSON error response of a plain route, with named examples.
     *
     * @param  string|list<string>  $refs
     */
    private function plainErrorResponse(string $description, string|array $refs, array $examples): Response
    {
        return new Response(
            description: $description,
            content: ApiSchemas::content($refs, $examples),
        );
    }

    /**
     * The JSON body the purchased download route refuses a request with.
     */
    private function downloadRefusal(string $error): array
    {
        return [
            'message' => self::DOWNLOAD_REFUSALS[$error],
            'error' => $error,
        ];
    }
}

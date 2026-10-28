<?php

namespace Webkul\BagistoApi\OpenApi;

use ApiPlatform\OpenApi\Model\Components;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\OpenApi;

class AccessDocumenter
{
    /**
     * The HTTP methods an OpenAPI path item can hold an operation for.
     */
    private const HTTP_METHODS = ['get', 'put', 'post', 'delete', 'options', 'head', 'patch', 'trace'];

    /**
     * Document who may call each operation of a surface: its security schemes and requirements and shared errors.
     */
    public function __invoke(OpenApi $openApi, string $surface): OpenApi
    {
        $openApi = $this->withComponents($openApi, $surface);

        $paths = new Paths;

        foreach ($openApi->getPaths()->getPaths() as $path => $pathItem) {
            $paths->addPath($path, $this->documentPathItem($pathItem, (string) $path, $surface));
        }

        return $openApi
            ->withPaths($paths)
            ->withSecurity(OperationAccess::documentSecurity($surface));
    }

    /**
     * Replace the security schemes with the surface's own and add the shared schemas.
     */
    protected function withComponents(OpenApi $openApi, string $surface): OpenApi
    {
        $components = $openApi->getComponents() ?? new Components;

        $schemas = new \ArrayObject($components->getSchemas()?->getArrayCopy() ?? []);

        foreach ($this->sharedSchemas($surface) as $name => $schema) {
            if (! isset($schemas[$name])) {
                $schemas[$name] = new \ArrayObject($schema);
            }
        }

        return $openApi->withComponents(
            $components
                ->withSchemas($schemas)
                ->withSecuritySchemes(new \ArrayObject(OperationAccess::securitySchemes($surface))),
        );
    }

    /**
     * The component schemas the shared responses and the auth operations refer to.
     */
    protected function sharedSchemas(string $surface): array
    {
        if ($surface === 'admin') {
            return [
                'AuthError' => ApiSchemas::AUTH_ERROR,
                'ApiProblem' => ApiSchemas::PROBLEM,
                'AdminTokenPair' => ApiSchemas::ADMIN_TOKEN_PAIR,
            ];
        }

        return [
            'StorefrontKeyError' => ApiSchemas::STOREFRONT_KEY_ERROR,
            'ApiProblem' => ApiSchemas::PROBLEM,
        ];
    }

    /**
     * Document every operation of a path item.
     */
    protected function documentPathItem(PathItem $pathItem, string $path, string $surface): PathItem
    {
        foreach (self::HTTP_METHODS as $method) {
            $operation = $pathItem->{'get'.ucfirst($method)}();

            if (! $operation instanceof Operation) {
                continue;
            }

            $access = $surface === 'admin'
                ? OperationAccess::admin($path, $method)
                : OperationAccess::shop($path, $method);

            $operation = $operation->withSecurity(OperationAccess::security($surface, $access));

            $operation = $this->withSharedResponses($operation, $this->sharedResponses($surface, $access, $path));

            $pathItem = $pathItem->{'with'.ucfirst($method)}($operation);
        }

        return $pathItem;
    }

    /**
     * Add each shared response the operation lacks, and give a documented one without a body the shared body.
     *
     * @param  array<int, array{new: Response, fill: \ArrayObject}>  $shared
     */
    protected function withSharedResponses(Operation $operation, array $shared): Operation
    {
        $responses = $operation->getResponses() ?? [];

        foreach ($shared as $status => $response) {
            $existing = $responses[$status] ?? null;

            if ($existing === null) {
                $responses[$status] = $response['new'];

                continue;
            }

            if (
                $existing instanceof Response
                && count($existing->getContent() ?? []) === 0
            ) {
                $responses[$status] = $existing->withContent($response['fill']);
            }
        }

        ksort($responses);

        return $operation->withResponses($responses);
    }

    /**
     * The 401, 403 and 404 responses an operation of this access level can answer with.
     *
     * @return array<int, array{new: Response, fill: \ArrayObject}>
     */
    protected function sharedResponses(string $surface, string $access, string $path): array
    {
        if (
            $surface === 'admin'
            && $access === OperationAccess::PUBLIC
        ) {
            return [];
        }

        $responses = $surface === 'admin'
            ? $this->adminResponses($access)
            : $this->shopResponses($access);

        if ($this->isItemPath($path)) {
            $content = ApiSchemas::content(ApiSchemas::PROBLEM_REF, []);

            $responses[404] = [
                'new' => new Response('No record with this ID exists, or it is not one the caller may see. `detail` names what was not found.', $content),
                'fill' => $content,
            ];
        }

        return $responses;
    }

    /**
     * The shared 401 and 403 responses of an admin operation.
     *
     * @return array<int, array{new: Response, fill: \ArrayObject}>
     */
    protected function adminResponses(string $access): array
    {
        $unauthenticated = [
            'unauthenticated' => [
                'message' => 'Unauthenticated.',
                'error' => 'unauthenticated',
            ],

            'access_token_expired' => [
                'message' => 'The access token has expired. Refresh it to continue.',
                'error' => 'access_token_expired',
            ],
        ];

        if ($access === OperationAccess::ADMIN_SESSION) {
            $unauthorized = ApiSchemas::content(ApiSchemas::AUTH_ERROR_REF, $unauthenticated);

            $forbidden = ApiSchemas::content(ApiSchemas::AUTH_ERROR_REF, [
                'access_token_required' => [
                    ...ApiSchemas::problem(403, 'Forbidden', 'This action needs the access token of a login session; API tokens cannot use it.'),
                    'message' => 'This action needs the access token of a login session; API tokens cannot use it.',
                    'error' => 'access_token_required',
                ],
            ]);

            return [
                401 => [
                    'new' => new Response('The access token is missing or invalid, or its session was revoked (`unauthenticated`), or it expired (`access_token_expired`): refresh it and retry.', $unauthorized),
                    'fill' => $unauthorized,
                ],

                403 => [
                    'new' => new Response('Called with an API token (`access_token_required`); only a login session\'s access token may call this operation.', $forbidden),
                    'fill' => $forbidden,
                ],
            ];
        }

        $unauthorized = ApiSchemas::content(ApiSchemas::AUTH_ERROR_REF, [
            ...$unauthenticated,

            'api_token_revoked' => [
                'message' => 'The API token has been revoked.',
                'error' => 'api_token_revoked',
            ],

            'api_token_expired' => [
                'message' => 'The API token has expired.',
                'error' => 'api_token_expired',
            ],
        ]);

        $forbidden = ApiSchemas::content([ApiSchemas::PROBLEM_REF, ApiSchemas::AUTH_ERROR_REF], [
            'no_permission' => ApiSchemas::problem(403, 'Forbidden', 'You do not have permission to perform this action.'),

            'api_token_domain_mismatch' => [
                'message' => 'The API token cannot be used from this domain.',
                'error' => 'api_token_domain_mismatch',
            ],
        ]);

        return [
            401 => [
                'new' => new Response('The bearer token is missing or invalid, its session was revoked or its admin is inactive (`unauthenticated`), the access token expired (`access_token_expired`: refresh and retry), or the API token expired or was revoked (`api_token_expired`, `api_token_revoked`).', $unauthorized),
                'fill' => $unauthorized,
            ],

            403 => [
                'new' => new Response('The admin\'s role lacks a permission this operation checks (a problem document), or an API token was used from a domain it is not bound to (`api_token_domain_mismatch`).', $forbidden),
                'fill' => $forbidden,
            ],
        ];
    }

    /**
     * The shared 401 and 403 responses of a storefront operation.
     *
     * @return array<int, array{new: Response, fill: \ArrayObject}>
     */
    protected function shopResponses(string $access): array
    {
        $missingKey = [
            'message' => 'X-STOREFRONT-KEY header is required',
            'error' => 'missing_key',
            'header_name' => 'X-STOREFRONT-KEY',
            'key_type' => 'shop',
        ];

        $invalidKey = [
            'message' => 'Invalid or inactive shop API key',
            'error' => 'invalid_key',
        ];

        $keyOnly = in_array($access, [OperationAccess::PUBLIC, OperationAccess::OPTIONAL_CUSTOMER], true);

        $both = [ApiSchemas::STOREFRONT_KEY_ERROR_REF, ApiSchemas::PROBLEM_REF];

        if ($keyOnly) {
            $unauthorized = ApiSchemas::content(ApiSchemas::STOREFRONT_KEY_ERROR_REF, ['missing_key' => $missingKey]);

            $forbidden = ApiSchemas::content(ApiSchemas::STOREFRONT_KEY_ERROR_REF, ['invalid_key' => $invalidKey]);

            return [
                401 => [
                    'new' => new Response('The `X-STOREFRONT-KEY` header is missing (`missing_key`).', $unauthorized),
                    'fill' => ApiSchemas::content($both, ['missing_key' => $missingKey]),
                ],

                403 => [
                    'new' => new Response('The storefront key is invalid or inactive, or not allowed from this IP address (`invalid_key`).', $forbidden),
                    'fill' => ApiSchemas::content($both, ['invalid_key' => $invalidKey]),
                ],
            ];
        }

        $tokenMissing = $access === OperationAccess::CART
            ? ApiSchemas::problem(401, 'Unauthorized', 'Authentication token is required')
            : ApiSchemas::problem(401, 'Unauthorized', 'Authentication token is required. Please provide the token in the Authorization header as "Bearer <token>"');

        $notAllowed = $access === OperationAccess::CART
            ? ApiSchemas::problem(403, 'Forbidden', 'Unauthorized access to cart')
            : ApiSchemas::problem(403, 'Forbidden', 'Unauthenticated. Please login to perform this action');

        $unauthorized = ApiSchemas::content($both, [
            'missing_key' => $missingKey,
            'token_missing' => $tokenMissing,
        ]);

        $forbidden = ApiSchemas::content($both, [
            'invalid_key' => $invalidKey,
            'not_allowed' => $notAllowed,
        ]);

        $bearer = $access === OperationAccess::CART
            ? 'no bearer token (a guest cart token or a customer token) was sent'
            : 'the customer token is missing or invalid';

        return [
            401 => [
                'new' => new Response('The `X-STOREFRONT-KEY` header is missing (`missing_key`), or '.$bearer.' (a problem document).', $unauthorized),
                'fill' => $unauthorized,
            ],

            403 => [
                'new' => new Response('The storefront key is invalid or inactive (`invalid_key`), or the bearer may not act on this record (a problem document). Several customer operations answer a missing customer token with 403 rather than 401.', $forbidden),
                'fill' => $forbidden,
            ],
        ];
    }

    /**
     * Whether the path addresses one record: its last segment is a path parameter.
     */
    protected function isItemPath(string $path): bool
    {
        return str_ends_with($path, '}');
    }
}

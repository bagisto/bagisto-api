<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\FragmentDefinitionNode;
use GraphQL\Language\AST\FragmentSpreadNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\AST\SelectionSetNode;
use GraphQL\Language\Parser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Webkul\BagistoApi\Attributes\AllowPublic;
use Webkul\BagistoApi\Attributes\RequiresStorefrontKey;
use Webkul\BagistoApi\Services\ApiKeyService;

/**
 * Validates API keys for GraphQL operations using attributes
 */
class VerifyGraphQLStorefrontKey
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(
        protected ApiKeyService $apiKeyService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        if (! str_starts_with($path, '/api/graphql') && ! str_starts_with($path, '/graphql')) {
            return $next($request);
        }

        $getQuery = $request->query('query');

        if ($request->isMethod('GET') && (! is_string($getQuery) || trim($getQuery) === '')) {
            $response = $next($request);

            if (str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
                $response = $this->injectHeaderScript($response);
            }

            return $response;
        }

        $isAdminRequest = str_starts_with($path, '/admin/graphql') ||
                         $request->header('X-Admin-Key') !== null;

        $queries = $this->extractQueries($request);

        if (! $this->requiresAuthentication($queries)) {
            return $next($request);
        }

        $keyType = $isAdminRequest ? ApiKeyService::KEY_TYPE_ADMIN : ApiKeyService::KEY_TYPE_SHOP;
        $headerName = $keyType === ApiKeyService::KEY_TYPE_ADMIN ? 'X-Admin-Key' : 'X-STOREFRONT-KEY';

        $key = ApiKeyService::getKeyFromRequest($request, $keyType);

        if (! $key) {
            return $this->sendAuthenticationError(
                "$headerName header is required for this operation",
                'missing_key',
                $headerName,
                $keyType
            );
        }

        $ipAddress = $request->ip();
        $validation = $this->apiKeyService->validate($key, $keyType, $ipAddress);

        if (! $validation['valid']) {
            return $this->sendAuthenticationError('Invalid API key', 'invalid_key', $headerName, $keyType);
        }

        $client = $validation['client'];
        $rateLimit = $this->apiKeyService->checkRateLimit($client, $ipAddress);

        if (! $rateLimit['allowed']) {
            return $this->sendRateLimitError($rateLimit);
        }

        $request->attributes->set('storefront_key', $client);
        $request->attributes->set('rate_limit', $rateLimit);
        $request->attributes->set('key_type', $keyType);

        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', (string) ($rateLimit['limit'] ?? config('storefront.default_rate_limit', 100)));
        $response->headers->set('X-RateLimit-Remaining', (string) ($rateLimit['remaining'] ?? 0));
        $response->headers->set('X-RateLimit-Reset', (string) ($rateLimit['reset_at'] ?? 0));

        return $response;
    }

    /**
     * Collect every query document API Platform could execute for this request.
     *
     * API Platform reads `?query=` on any method and lets a JSON body, a raw
     * `application/graphql` body or a multipart `operations` field override it,
     * so each of those sources is checked rather than the JSON body alone.
     *
     * @return array<int, string>
     */
    protected function extractQueries(Request $request): array
    {
        $queries = [$request->query('query')];

        if ($request->isMethod('POST')) {
            $body = $request->getContent();
            $contentType = strtolower((string) $request->headers->get('Content-Type', ''));

            $data = json_decode($body, true);

            if (is_array($data)) {
                $queries[] = $data['query'] ?? null;
            }

            if (str_contains($contentType, 'graphql')) {
                $queries[] = $body;
            }

            $operations = $request->input('operations');

            if (is_string($operations)) {
                $operations = json_decode($operations, true);
            }

            if (is_array($operations)) {
                $queries[] = $operations['query'] ?? null;
            }
        }

        return array_values(array_filter($queries, fn ($query) => is_string($query) && trim($query) !== ''));
    }

    /**
     * Determine if the request's GraphQL documents require a storefront key.
     *
     * A document is public only when every root field of every operation in it is
     * an introspection field, `__typename`, a configured public operation, or a
     * resolver marked #[AllowPublic]. The client-chosen operation name is never
     * trusted, and a document that does not parse requires a key.
     *
     * @param  array<int, string>  $queries
     */
    protected function requiresAuthentication(array $queries): bool
    {
        foreach ($queries as $query) {
            try {
                $document = Parser::parse($query);
            } catch (Throwable) {
                return true;
            }

            $fields = $this->rootFieldNames($document);

            if ($fields === []) {
                return true;
            }

            foreach ($fields as $field) {
                if (! $this->isPublicField($field)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Collect the root field names of every operation, following fragments.
     *
     * @return array<int, string>
     */
    protected function rootFieldNames(DocumentNode $document): array
    {
        $fragments = [];

        foreach ($document->definitions as $definition) {
            if ($definition instanceof FragmentDefinitionNode) {
                $fragments[$definition->name->value] = $definition;
            }
        }

        $fields = [];

        foreach ($document->definitions as $definition) {
            if ($definition instanceof OperationDefinitionNode) {
                $this->collectFieldNames($definition->selectionSet, $fragments, $fields, []);
            }
        }

        return array_values(array_unique($fields));
    }

    /**
     * Walk a selection set, recording field names and descending into fragments.
     *
     * @param  array<string, FragmentDefinitionNode>  $fragments
     * @param  array<int, string>  $fields
     * @param  array<string, bool>  $visited
     */
    protected function collectFieldNames(SelectionSetNode $selectionSet, array $fragments, array &$fields, array $visited): void
    {
        foreach ($selectionSet->selections as $selection) {
            if ($selection instanceof FieldNode) {
                $fields[] = $selection->name->value;
            } elseif ($selection instanceof InlineFragmentNode) {
                $this->collectFieldNames($selection->selectionSet, $fragments, $fields, $visited);
            } elseif ($selection instanceof FragmentSpreadNode) {
                $name = $selection->name->value;

                if (isset($visited[$name]) || ! isset($fragments[$name])) {
                    continue;
                }

                $this->collectFieldNames($fragments[$name]->selectionSet, $fragments, $fields, $visited + [$name => true]);
            }
        }
    }

    /**
     * Determine whether a single root field may run without a storefront key.
     */
    protected function isPublicField(string $field): bool
    {
        if ($field === '__typename') {
            return true;
        }

        if (in_array($field, ['__schema', '__type'], true)) {
            return (bool) config('graphql-auth.allow_introspection', true);
        }

        if (in_array($field, config('graphql-auth.public_operations', []), true)) {
            return true;
        }

        return $this->checkOperationAttributes($field) === false;
    }

    /**
     * Check if a GraphQL operation has authentication attributes
     *
     * Searches for resolvers/mutations with the operation name and checks:
     * - #[AllowPublic] attribute → doesn't require auth (return false)
     * - #[RequiresStorefrontKey] attribute → requires auth (return true)
     *
     * @param  string|null  $operationName  The operation name to find
     * @return bool|null null if no attribute found, bool if found
     */
    protected function checkOperationAttributes(?string $operationName): ?bool
    {
        if (! $operationName) {
            return null;
        }

        try {
            $paths = [
                app_path('GraphQL/Queries'),
                app_path('GraphQL/Mutations'),
                app_path('GraphQL/Subscriptions'),
                app_path('Http/GraphQL/Queries'),
                app_path('Http/GraphQL/Mutations'),
                base_path('packages/*/src/GraphQL/Queries'),
                base_path('packages/*/src/GraphQL/Mutations'),
            ];

            foreach ($paths as $basePath) {
                if (strpos($basePath, '*') !== false) {
                    $dirs = glob($basePath, GLOB_BRACE);
                    foreach ($dirs as $dir) {
                        $result = $this->findOperationInDirectory($dir, $operationName);
                        if ($result !== null) {
                            return $result;
                        }
                    }
                } else {
                    if (is_dir($basePath)) {
                        $result = $this->findOperationInDirectory($basePath, $operationName);
                        if ($result !== null) {
                            return $result;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            if (config('graphql-auth.log_auth_checks', false)) {
                Log::debug('GraphQL auth attribute check failed: '.$e->getMessage());
            }
        }

        return null;
    }

    /**
     * Search for a GraphQL operation resolver in a directory and check its attributes
     */
    protected function findOperationInDirectory(string $directory, string $operationName): ?bool
    {
        if (! is_dir($directory)) {
            return null;
        }

        $patterns = [
            $operationName.'.php',
            ucfirst($operationName).'.php',
            strtolower($operationName).'.php',
        ];

        foreach ($patterns as $pattern) {
            $filepath = $directory.'/'.$pattern;
            if (file_exists($filepath)) {
                return $this->checkFileAttributes($filepath, $operationName);
            }
        }

        $files = glob($directory.'/*.php');
        foreach ($files as $filepath) {
            $result = $this->checkFileAttributes($filepath, $operationName);
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * Check attributes in a PHP file for a GraphQL operation
     */
    protected function checkFileAttributes(string $filepath, string $operationName): ?bool
    {
        try {
            $content = file_get_contents($filepath);

            if (preg_match('/namespace\s+([\w\\]+)/', $content, $nsMatches) &&
                preg_match('/class\s+(\w+)/', $content, $classMatches)) {

                $className = $nsMatches[1].'\\'.$classMatches[2];

                if (! class_exists($className)) {
                    return null;
                }

                $reflection = new ReflectionClass($className);

                foreach ($reflection->getAttributes(AllowPublic::class) as $attr) {
                    return false;
                }

                foreach ($reflection->getAttributes(RequiresStorefrontKey::class) as $attr) {
                    return true;
                }

                foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                    if ($this->methodNameMatches($method->getName(), $operationName)) {
                        foreach ($method->getAttributes(AllowPublic::class) as $attr) {
                            return false;
                        }

                        foreach ($method->getAttributes(RequiresStorefrontKey::class) as $attr) {
                            return true;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            if (config('graphql-auth.log_auth_checks', false)) {
                Log::debug("Failed to check attributes in {$filepath}: ".$e->getMessage());
            }
        }

        return null;
    }

    /**
     * Check if a method name matches the operation name
     * Handles different naming conventions (camelCase, snake_case, PascalCase)
     */
    protected function methodNameMatches(string $methodName, string $operationName): bool
    {
        if ($methodName === $operationName) {
            return true;
        }

        $camelToSnake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $methodName));
        $opSnake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $operationName));
        if ($camelToSnake === $opSnake) {
            return true;
        }

        if (strtolower($methodName) === strtolower($operationName)) {
            return true;
        }

        return false;
    }

    /**
     * Send authentication error response
     *
     * @param  string  $headerName  The header name required (X-STOREFRONT-KEY or X-Admin-Key)
     * @param  string  $keyType  The key type needed (shop or admin)
     */
    protected function sendAuthenticationError(
        string $message,
        string $error = 'missing_key',
        string $headerName = 'X-STOREFRONT-KEY',
        string $keyType = 'shop'
    ): JsonResponse {
        return response()->json([
            'message' => $message,
            'error' => $error,
            'header_name' => $headerName,
            'key_type' => $keyType,
            'errors' => [
                [
                    'message' => $message,
                    'extensions' => [
                        'code' => 'UNAUTHENTICATED',
                    ],
                ],
            ],
        ], 401);
    }

    /**
     * Send the rate-limit-exceeded response.
     */
    protected function sendRateLimitError(array $rateLimit): JsonResponse
    {
        $retryAfter = (int) ($rateLimit['reset_at'] ?? 60);

        return response()->json([
            'message' => 'Rate limit exceeded',
            'error' => 'rate_limit_exceeded',
            'retry_after' => $retryAfter,
            'errors' => [
                [
                    'message' => 'Rate limit exceeded',
                    'extensions' => [
                        'code' => 'RATE_LIMITED',
                    ],
                ],
            ],
        ], 429, [
            'Retry-After' => (string) $retryAfter,
            'X-RateLimit-Limit' => (string) ($rateLimit['limit'] ?? 0),
            'X-RateLimit-Remaining' => '0',
            'X-RateLimit-Reset' => (string) $retryAfter,
        ]);
    }

    /**
     * Inject X-STOREFRONT-KEY header script into API Platform's GraphiQL HTML
     * Intercepts fetch requests and adds the header automatically
     */
    protected function injectHeaderScript(Response $response): Response
    {
        $storefrontKey = config('storefront.playground_key') ?: 'pk_storefront_xxxxx';

        $script = <<<'JS'
<script>
(function() {
    const storefrontKey = localStorage.getItem('bagisto-api-key') || 'JS' . $storefrontKey . 'JS';
    const originalFetch = window.fetch;
    
    // Override fetch to inject X-STOREFRONT-KEY header
    window.fetch = function(...args) {
        let options = args[1] || {};
        options.headers = options.headers || {};
        options.headers['X-STOREFRONT-KEY'] = storefrontKey;
        return originalFetch.apply(this, [args[0], options]);
    };

    // Allow users to edit the key via browser console or dev tools
    window.setBagistoApiKey = function(key) {
        localStorage.setItem('bagisto-api-key', key);
        console.log('Bagisto API Key updated:', key);
    };

    window.getBagistoApiKey = function() {
        return localStorage.getItem('bagisto-api-key') || storefrontKey;
    };

    console.log('%cBagisto GraphQL API', 'color: #667eea; font-size: 14px; font-weight: bold;');
    console.log('X-STOREFRONT-KEY: ' + getBagistoApiKey());
    console.log('Change key with: setBagistoApiKey("your-key-here")');
})();
</script>
JS;

        $content = $response->getContent();
        $content = str_replace('</body>', $script.'</body>', $content);
        $response->setContent($content);

        return $response;
    }
}

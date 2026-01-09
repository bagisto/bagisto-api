<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response;
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

        // Only apply to GraphQL endpoints
        if (! str_starts_with($path, '/api/graphql') && ! str_starts_with($path, '/graphql')) {
            return $next($request);
        }

        // For GET requests, inject X-STOREFRONT-KEY header into API Platform's GraphiQL
        if ($request->method() === 'GET') {
            $response = $next($request);

            // If this is HTML (GraphiQL interface), inject header injection script
            if (str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
                $response = $this->injectHeaderScript($response);
            }

            return $response;
        }

        // Determine if this is an admin GraphQL request
        $isAdminRequest = str_starts_with($path, '/admin/graphql') ||
                         $request->header('X-Admin-Key') !== null;

        // Get the request body to check operation type
        $body = $request->getContent();

        try {
            $data = json_decode($body, true) ?? [];
            $query = $data['query'] ?? '';
            $operationName = $data['operationName'] ?? null;

            // Check if operation requires authentication using attributes or config
            if (! $this->requiresAuthentication($query, $operationName)) {
                return $next($request);
            }
        } catch (\Exception $e) {
            // If we can't parse the query, require authentication
            return $this->sendAuthenticationError('Invalid request body');
        }

        // Determine which key type is needed
        $keyType = $isAdminRequest ? ApiKeyService::KEY_TYPE_ADMIN : ApiKeyService::KEY_TYPE_SHOP;
        $headerName = $keyType === ApiKeyService::KEY_TYPE_ADMIN ? 'X-Admin-Key' : 'X-STOREFRONT-KEY';

        // Get the appropriate API key from request
        $key = ApiKeyService::getKeyFromRequest($request, $keyType);

        if (! $key) {
            return $this->sendAuthenticationError(
                "$headerName header is required for this operation",
                'missing_key',
                $headerName,
                $keyType
            );
        }

        // Validate the key
        $ipAddress = $request->ip();
        $validation = $this->apiKeyService->validate($key, $keyType, $ipAddress);

        if (! $validation['valid']) {
            return $this->sendAuthenticationError('Invalid API key', 'invalid_key', $headerName, $keyType);
        }

        // Check rate limit
        $client = $validation['client'];
        $rateLimit = $this->apiKeyService->checkRateLimit($client);

        if (! $rateLimit['allowed']) {
            return response()->json([
                'message'     => 'Rate limit exceeded',
                'error'       => 'rate_limit_exceeded',
                'retry_after' => $rateLimit['reset_at'],
            ], 429);
        }

        // Store in request for downstream use
        $request->attributes->set('storefront_key', $client);
        $request->attributes->set('rate_limit', $rateLimit);
        $request->attributes->set('key_type', $keyType);

        $response = $next($request);

        // Add rate limit headers if response supports them
        if (isset($rateLimit) && method_exists($response, 'header')) {
            $response->header('X-RateLimit-Limit', $rateLimit['limit'] ?? 100);
            $response->header('X-RateLimit-Remaining', $rateLimit['remaining'] ?? 0);
            $response->header('X-RateLimit-Reset', $rateLimit['reset_at'] ?? 0);
        }

        return $response;
    }

    /**
     * Determine if a GraphQL operation requires authentication
     *
     * Uses attribute-based configuration for API Platform:
     * - #[AllowPublic] marks operations that don't need authentication
     * - #[RequiresStorefrontKey] marks operations that need authentication
     * - Everything else defaults to requiring authentication (secure by default)
     *
     * @param  string  $query  The GraphQL query/mutation string
     * @param  string|null  $operationName  The operation name from request
     */
    protected function requiresAuthentication(string $query, ?string $operationName): bool
    {
        // Allow empty queries
        if (empty(trim($query))) {
            return false;
        }

        // Extract operation name from query if not provided
        if (! $operationName) {
            $operationName = $this->extractOperationName($query);
        }

        // Check for introspection patterns (allow for playground)
        if (config('graphql-auth.allow_introspection', true)) {
            if (strpos($query, '__schema') !== false || strpos($query, '__type') !== false) {
                return false;
            }
        }

        // First, try to find resolver using operation name and check attributes
        $operationAuth = $this->checkOperationAttributes($operationName);
        if ($operationAuth !== null) {
            return $operationAuth;
        }

        // Fallback to config-based approach
        $publicOperations = config('graphql-auth.public_operations', []);
        if ($operationName && in_array($operationName, $publicOperations, true)) {
            return false;
        }

        // Default: require authentication (secure by default)
        return true;
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
            // Search in common GraphQL resolver directories
            $paths = [
                app_path('GraphQL/Queries'),
                app_path('GraphQL/Mutations'),
                app_path('GraphQL/Subscriptions'),
                app_path('Http/GraphQL/Queries'),
                app_path('Http/GraphQL/Mutations'),
                // Bagisto packages
                base_path('packages/*/src/GraphQL/Queries'),
                base_path('packages/*/src/GraphQL/Mutations'),
            ];

            foreach ($paths as $basePath) {
                // Handle glob patterns
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
            // If reflection fails, fall back to config
            if (config('graphql-auth.log_auth_checks', false)) {
                \Log::debug('GraphQL auth attribute check failed: '.$e->getMessage());
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

        // Look for files that might match the operation
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

        // Also check all PHP files in the directory
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
            // Get the class name from file
            $content = file_get_contents($filepath);

            // Extract namespace and class name
            if (preg_match('/namespace\s+([\w\\]+)/', $content, $nsMatches) &&
                preg_match('/class\s+(\w+)/', $content, $classMatches)) {

                $className = $nsMatches[1].'\\'.$classMatches[2];

                if (! class_exists($className)) {
                    return null;
                }

                $reflection = new ReflectionClass($className);

                // Check class-level attributes
                foreach ($reflection->getAttributes(AllowPublic::class) as $attr) {
                    return false; // AllowPublic = no auth needed
                }

                foreach ($reflection->getAttributes(RequiresStorefrontKey::class) as $attr) {
                    return true; // RequiresStorefrontKey = auth needed
                }

                // Check method-level attributes (for method-based resolvers)
                foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                    // Check if method name matches operation name (camelCase, snake_case, etc)
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
                \Log::debug("Failed to check attributes in {$filepath}: ".$e->getMessage());
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
        // Direct match
        if ($methodName === $operationName) {
            return true;
        }

        // CamelCase vs snake_case
        $camelToSnake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $methodName));
        $opSnake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $operationName));
        if ($camelToSnake === $opSnake) {
            return true;
        }

        // Case-insensitive match
        if (strtolower($methodName) === strtolower($operationName)) {
            return true;
        }

        return false;
    }

    /**
     * Extract the operation name from a GraphQL query
     * Handles queries like:
     *   query GetProducts { ... }
     *   mutation AddToCart { ... }
     *   GetProducts { ... }
     */
    protected function extractOperationName(string $query): ?string
    {
        // Remove whitespace for easier matching
        $query = preg_replace('/\s+/', ' ', $query);

        // Match "query OperationName" or "mutation OperationName" or just "OperationName"
        if (preg_match('/(?:query|mutation|subscription)\s+(\w+)/i', $query, $matches)) {
            return $matches[1];
        }

        // Try to find operation name without query/mutation/subscription keyword
        if (preg_match('/^\s*(\w+)\s*\{/', $query, $matches)) {
            return $matches[1];
        }

        return null;
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
    ): \Illuminate\Http\JsonResponse {
        return response()->json([
            'message'     => $message,
            'error'       => $error,
            'header_name' => $headerName,
            'key_type'    => $keyType,
            'errors'      => [
                [
                    'message'    => $message,
                    'extensions' => [
                        'code' => 'UNAUTHENTICATED',
                    ],
                ],
            ],
        ], 401);
    }

    /**
     * Inject X-STOREFRONT-KEY header script into API Platform's GraphiQL HTML
     * Intercepts fetch requests and adds the header automatically
     */
    protected function injectHeaderScript(Response $response): Response
    {
        $storefrontKey = env('STOREFRONT_PLAYGROUND_KEY') ?? 'pk_storefront_xxxxx';

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
        // Inject script before closing </body> tag
        $content = str_replace('</body>', $script.'</body>', $content);
        $response->setContent($content);

        return $response;
    }
}

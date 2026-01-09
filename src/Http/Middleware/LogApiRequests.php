<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Jobs\LogApiRequestJob;

/**
 * Logs all API requests for security audit trail and monitoring
 */
class LogApiRequests
{
    /**
     * Paths to exclude from logging
     */
    private const EXCLUDED_PATHS = [
        'health',
        'ping',
        'docs',
        'graphiql',
        'swagger-ui',
        'docs.json',
        '.well-known',
    ];

    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $response = $next($request);
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        if ($this->shouldLog($request, $response)) {
            $this->logRequestAsync($request, $response, $duration);
        }

        return $response;
    }

    private function shouldLog(Request $request, $response): bool
    {
        foreach (self::EXCLUDED_PATHS as $path) {
            if (str_contains($request->path(), $path)) {
                return false;
            }
        }

        return str_starts_with($request->path(), 'api/');
    }

    private function logRequestAsync(Request $request, $response, float $duration): void
    {
        $logData = [
            'method'            => $request->getMethod(),
            'path'              => $request->path(),
            'status'            => $response->getStatusCode(),
            'duration_ms'       => $duration,
            'ip'                => $request->ip(),
            'user_agent'        => substr($request->userAgent() ?? '', 0, 255),
            'user_id'           => auth()->id(),
            'api_key'           => $this->maskApiKey($request->header('X-STOREFRONT-KEY')),
            'graphql_operation' => $this->getGraphQLOperation($request),
        ];

        try {
            if (class_exists(LogApiRequestJob::class)) {
                dispatch(new LogApiRequestJob($logData))->onQueue('api-logs');
            } else {
                $this->logSync($logData);
            }
        } catch (\Throwable $e) {
            $this->logSync($logData);
        }
    }

    private function logSync(array $logData): void
    {
        $level = $this->getLogLevel($logData['status']);
        Log::channel('api')->log($level, 'API Request', $logData);
    }

    private function getLogLevel(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return 'error';
        }

        if ($statusCode >= 400) {
            return 'warning';
        }

        return 'info';
    }

    private function maskApiKey(?string $apiKey): string
    {
        if (! $apiKey || strlen($apiKey) < 6) {
            return 'none';
        }

        return substr($apiKey, 0, 3).'***'.substr($apiKey, -3);
    }

    private function getGraphQLOperation(Request $request): ?string
    {
        if ($request->path() !== 'api/graphql') {
            return null;
        }

        $input = $request->json('operationName')
            ?? $this->extractOperationName($request->json('query') ?? '');

        return $input;
    }

    private function extractOperationName(string $query): ?string
    {
        if (preg_match('/^\s*(query|mutation|subscription)\s+(\w+)/i', $query, $matches)) {
            return strtolower($matches[1]).': '.$matches[2];
        }

        return null;
    }
}

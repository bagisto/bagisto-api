<?php

namespace Webkul\BagistoApi\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Jobs\LogApiRequestJob;

class LogApiRequests
{
    /**
     * Paths to exclude from logging.
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

    /**
     * Handle the request.
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $response = $next($request);
        $duration = round((microtime(true) - $startTime) * 1000, 2);

        if (
            $this->setting('enabled', true)
            && $this->shouldLog($request, $response)
        ) {
            $this->logRequestAsync($request, $response, $duration);
        }

        return $response;
    }

    /**
     * Read an api_logging option from config, falling back to the built-in default.
     */
    private function setting(string $key, mixed $default): mixed
    {
        return config('api-platform.api_logging.'.$key, $default);
    }

    /**
     * Should log.
     */
    private function shouldLog(Request $request, $response): bool
    {
        foreach ((array) $this->setting('exclude_paths', self::EXCLUDED_PATHS) as $path) {
            if (str_contains($request->path(), $path)) {
                return false;
            }
        }

        return str_starts_with($request->path(), 'api/');
    }

    /**
     * Log request async.
     */
    private function logRequestAsync(Request $request, $response, float $duration): void
    {
        $logData = [
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'ip' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 255),
            'user_id' => auth()->id(),
            'api_key' => $this->maskApiKey($request->header('X-STOREFRONT-KEY')),
            'graphql_operation' => $this->getGraphQLOperation($request),
        ];

        try {
            if (
                app()->environment('testing')
                || ! $this->setting('async', true)
            ) {
                $this->logSync($logData);
            } elseif (class_exists(LogApiRequestJob::class)) {
                dispatch(new LogApiRequestJob($logData))->onQueue($this->setting('queue', 'api-logs'));
            } else {
                $this->logSync($logData);
            }
        } catch (\Throwable $exception) {
            $this->logSync($logData);
        }
    }

    /**
     * Log sync.
     */
    private function logSync(array $logData): void
    {
        $level = $this->getLogLevel($logData['status']);

        try {
            $channel = $this->setting('channel', 'api');

            if (
                $channel
                && config('logging.channels.'.$channel)
            ) {
                Log::channel($channel)->log($level, 'API Request', $logData);
            } else {
                Log::log($level, 'API Request', $logData);
            }
        } catch (\Throwable $exception) {
            Log::log($level, 'API Request', $logData);
        }
    }

    /**
     * Get log level.
     */
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

    /**
     * Mask api key.
     */
    private function maskApiKey(?string $apiKey): string
    {
        if (
            ! $apiKey
            || strlen($apiKey) < 6
        ) {
            return 'none';
        }

        return substr($apiKey, 0, 3).'***'.substr($apiKey, -3);
    }

    /**
     * Get graph qloperation.
     */
    private function getGraphQLOperation(Request $request): ?string
    {
        if ($request->path() !== 'api/graphql') {
            return null;
        }

        $input = $request->json('operationName')
            ?? $this->extractOperationName($request->json('query') ?? '');

        return $input;
    }

    /**
     * Extract operation name.
     */
    private function extractOperationName(string $query): ?string
    {
        if (preg_match('/^\s*(query|mutation|subscription)\s+(\w+)/i', $query, $matches)) {
            return strtolower($matches[1]).': '.$matches[2];
        }

        return null;
    }
}

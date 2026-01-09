<?php

namespace Webkul\BagistoApi\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

/**
 * Database Query Logging Service Provider
 *
 * Logs database queries for debugging and performance monitoring
 * Only enabled in development or when explicitly configured
 */
class DatabaseQueryLoggingProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Only log queries in development or when enabled
        if (! $this->shouldLogQueries()) {
            return;
        }

        DB::listen(function (QueryExecuted $query) {
            $this->logQuery($query);
        });
    }

    /**
     * Check if database query logging should be enabled
     */
    private function shouldLogQueries(): bool
    {
        // Enable in development environment
        if (app()->environment('local', 'development')) {
            return true;
        }

        // Enable if explicitly configured
        return config('database.log_queries', false) ||
               env('DB_QUERY_LOG_ENABLED', false);
    }

    /**
     * Log a database query
     */
    private function logQuery(QueryExecuted $query): void
    {
        // Log slow queries at warning level
        if ($query->time > config('database.slow_query_threshold', 1000)) {
            Log::warning('Slow SQL Query', [
                'query'      => $query->sql,
                'bindings'   => $query->bindings,
                'time_ms'    => $query->time,
                'connection' => $query->connectionName,
            ]);

            return;
        }

        // Log all queries at debug level
        Log::debug('SQL Query Executed', [
            'query'      => $query->sql,
            'bindings'   => $query->bindings,
            'time_ms'    => $query->time,
            'connection' => $query->connectionName,
        ]);
    }
}

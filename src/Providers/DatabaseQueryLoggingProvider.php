<?php

namespace Webkul\BagistoApi\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class DatabaseQueryLoggingProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (! $this->shouldLogQueries()) {
            return;
        }

        DB::listen(function (QueryExecuted $query) {
            $this->logQuery($query);
        });
    }

    /**
     * Check if database query logging should be enabled.
     */
    private function shouldLogQueries(): bool
    {
        return (bool) config('api-platform.database.log_queries', false);
    }

    /**
     * Log a database query.
     */
    private function logQuery(QueryExecuted $query): void
    {
        $context = [
            'query' => $query->sql,
            'time_ms' => $query->time,
            'connection' => $query->connectionName,
        ];

        if (config('api-platform.database.log_bindings', false)) {
            $context['bindings'] = $query->bindings;
        }

        if ($query->time > config('api-platform.database.slow_query_threshold', 1000)) {
            Log::warning('Slow SQL Query', $context);

            return;
        }

        Log::debug('SQL Query Executed', $context);
    }
}

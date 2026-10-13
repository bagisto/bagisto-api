<?php

namespace Webkul\BagistoApi\Tests\Feature;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Providers\DatabaseQueryLoggingProvider;
use Webkul\BagistoApi\Tests\BagistoApiTest;

class DatabaseQueryLoggingProviderTest extends BagistoApiTest
{
    /**
     * Boot the provider and fire one query event.
     */
    private function fireQuery(): void
    {
        (new DatabaseQueryLoggingProvider($this->app))->boot();

        Event::dispatch(new QueryExecuted('select * from t where a = ?', ['secret'], 1.0, $this->app['db']->connection()));
    }

    public function test_nothing_is_logged_in_a_local_environment_without_the_flag(): void
    {
        config(['api-platform.database.log_queries' => false]);
        $this->app['env'] = 'local';

        Log::shouldReceive('debug')->never();
        Log::shouldReceive('warning')->never();

        $this->fireQuery();
    }

    public function test_bindings_are_omitted_unless_explicitly_enabled(): void
    {
        config([
            'api-platform.database.log_queries' => true,
            'api-platform.database.log_bindings' => false,
        ]);

        Log::shouldReceive('debug')->once()->withArgs(fn ($message, $context) => ! array_key_exists('bindings', $context));

        $this->fireQuery();
    }

    public function test_bindings_are_logged_when_enabled(): void
    {
        config([
            'api-platform.database.log_queries' => true,
            'api-platform.database.log_bindings' => true,
        ]);

        Log::shouldReceive('debug')->once()->withArgs(fn ($message, $context) => $context['bindings'] === ['secret']);

        $this->fireQuery();
    }
}

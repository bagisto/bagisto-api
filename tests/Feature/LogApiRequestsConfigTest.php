<?php

namespace Webkul\BagistoApi\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Webkul\BagistoApi\Http\Middleware\LogApiRequests;
use Webkul\BagistoApi\Tests\BagistoApiTest;

class LogApiRequestsConfigTest extends BagistoApiTest
{
    /**
     * Run a request through the middleware.
     */
    private function send(string $path): void
    {
        (new LogApiRequests)->handle(Request::create($path), fn () => new Response('ok'));
    }

    public function test_logs_when_enabled(): void
    {
        config(['api-platform.api_logging.enabled' => true, 'logging.channels.api' => null]);

        Log::shouldReceive('log')->once();

        $this->send('/api/shop/products');
    }

    public function test_does_not_log_when_disabled_in_config(): void
    {
        config(['api-platform.api_logging.enabled' => false]);

        Log::shouldReceive('log')->never();

        $this->send('/api/shop/products');
    }

    public function test_exclude_paths_come_from_config(): void
    {
        config([
            'api-platform.api_logging.enabled' => true,
            'api-platform.api_logging.exclude_paths' => ['products'],
        ]);

        Log::shouldReceive('log')->never();

        $this->send('/api/shop/products');
    }
}

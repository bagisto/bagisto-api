<?php

namespace Webkul\BagistoApi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Queued job for asynchronous API request logging
 */
class LogApiRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $logData
    ) {
        $this->queue = 'api-logs';
        $this->timeout = 30;
    }

    public function handle(): void
    {
        try {
            $level = $this->getLogLevel($this->logData['status']);
            
            // Use 'api' channel if configured, otherwise fall back to default
            try {
                if (config('logging.channels.api')) {
                    Log::channel('api')->log($level, 'API Request', $this->logData);
                } else {
                    Log::log($level, 'API Request', $this->logData);
                }
            } catch (\Throwable $e) {
                // If channel logging fails, use default logger
                Log::log($level, 'API Request', $this->logData);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to log API request', [
                'error'    => $e->getMessage(),
                'log_data' => $this->logData,
            ]);

            throw $e;
        }
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

    public function failed(\Throwable $exception): void
    {
        Log::critical('API request logging job failed', [
            'error'    => $exception->getMessage(),
            'log_data' => $this->logData,
            'attempts' => $this->attempts(),
        ]);
    }
}

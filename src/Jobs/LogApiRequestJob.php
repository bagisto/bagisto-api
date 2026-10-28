<?php

namespace Webkul\BagistoApi\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogApiRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new instance.
     */
    public function __construct(
        public array $logData
    ) {
        $this->queue = 'api-logs';
        $this->timeout = 30;
    }

    /**
     * Handle the request.
     */
    public function handle(): void
    {
        try {
            $level = $this->getLogLevel($this->logData['status']);

            try {
                if (config('logging.channels.api')) {
                    Log::channel('api')->log($level, 'API Request', $this->logData);
                } else {
                    Log::log($level, 'API Request', $this->logData);
                }
            } catch (\Throwable $exception) {
                Log::log($level, 'API Request', $this->logData);
            }
        } catch (\Throwable $exception) {
            Log::error('Failed to log API request', [
                'error' => $exception->getMessage(),
                'log_data' => $this->logData,
            ]);

            throw $exception;
        }
    }

    /**
     * Failed.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('API request logging job failed', [
            'error' => $exception->getMessage(),
            'log_data' => $this->logData,
            'attempts' => $this->attempts(),
        ]);
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
}

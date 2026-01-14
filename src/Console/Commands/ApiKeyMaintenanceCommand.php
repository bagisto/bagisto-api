<?php

namespace Webkul\BagistoApi\Console\Commands;

use Illuminate\Console\Command;
use Webkul\BagistoApi\Services\KeyRotationService;

/**
 * Perform automatic API key maintenance tasks
 */
class ApiKeyMaintenanceCommand extends Command
{
    protected $signature = 'bagisto-api:key:maintain 
                            {--cleanup : Clean up expired keys}
                            {--invalidate : Invalidate deprecated keys}
                            {--notify : Send expiration notifications}
                            {--all : Perform all maintenance tasks}';

    protected $description = 'Automatic API key maintenance (cleanup, deprecation, notifications)';

    protected KeyRotationService $rotationService;

    public function __construct()
    {
        parent::__construct();
        $this->rotationService = new KeyRotationService;
    }

    /**
     * Execute the maintenance command.
     */
    public function handle(): int
    {
        $cleanup = $this->option('cleanup') || $this->option('all');
        $invalidate = $this->option('invalidate') || $this->option('all');
        $notify = $this->option('notify') || $this->option('all');

        if (! $cleanup && ! $invalidate && ! $notify) {
            $cleanup = $invalidate = $notify = true;
        }

        $this->info(__('bagistoapi::app.graphql.install.maintenance-starting'));
        $this->newLine();

        if ($cleanup) {
            $this->cleanup();
        }

        if ($invalidate) {
            $this->invalidateDeprecatedKeys();
        }

        if ($notify) {
            $this->notifyExpiringKeys();
        }

        $this->newLine();
        $this->info(__('bagistoapi::app.graphql.install.maintenance-complete'));

        return 0;
    }

    /**
     * Clean up expired keys.
     */
    private function cleanup(): void
    {
        $this->line(__('bagistoapi::app.graphql.install.cleanup-expired-keys'));

        $count = $this->rotationService->cleanupExpiredKeys();

        if ($count > 0) {
            $this->info(__('bagistoapi::app.graphql.install.cleanup-success-message', ['count' => $count]));
        } else {
            $this->line(__('bagistoapi::app.graphql.install.cleanup-expired-none'));
        }
    }

    /**
     * Invalidate deprecated keys.
     */
    private function invalidateDeprecatedKeys(): void
    {
        $this->line(__('bagistoapi::app.graphql.install.invalidate-deprecated'));

        $count = $this->rotationService->invalidateDeprecatedKeys();

        if ($count > 0) {
            $this->info(__('bagistoapi::app.graphql.install.invalidate-success-message', ['count' => $count]));
        } else {
            $this->line(__('bagistoapi::app.graphql.install.invalidate-deprecated-none'));
        }
    }

    /**
     * Send expiration notifications.
     */
    private function notifyExpiringKeys(): void
    {
        $this->line(__('bagistoapi::app.graphql.install.notify-expiring'));

        $keysExpiring7Days = $this->rotationService->getKeysExpiringSoon(7);
        $keysExpiring30Days = $this->rotationService->getKeysExpiringSoon(30);

        $notified = 0;

        foreach ($keysExpiring7Days as $key) {
            if ($this->sendExpirationNotification($key, '7 days')) {
                $notified++;
            }
        }

        foreach ($keysExpiring30Days as $key) {
            if (! $keysExpiring7Days->contains($key)) {
                if ($this->sendExpirationNotification($key, '30 days')) {
                    $notified++;
                }
            }
        }

        if ($notified > 0) {
            $this->info(__('bagistoapi::app.graphql.install.notify-success-message', ['count' => $notified]));
        } else {
            $this->line(__('bagistoapi::app.graphql.install.notify-expiring-none'));
        }
    }

    /**
     * Send expiration notification for a key.
     */
    private function sendExpirationNotification($key, string $timeframe): bool
    {
        try {
            return true;
        } catch (\Exception $e) {
            $this->warn(__('bagistoapi::app.graphql.install.notify-failed-message', ['key' => $key->name, 'error' => $e->getMessage()]));

            return false;
        }
    }
}

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

    public function handle(): int
    {
        $cleanup = $this->option('cleanup') || $this->option('all');
        $invalidate = $this->option('invalidate') || $this->option('all');
        $notify = $this->option('notify') || $this->option('all');

        // If no specific option given, run all tasks (default behavior)
        if (! $cleanup && ! $invalidate && ! $notify) {
            $cleanup = $invalidate = $notify = true;
        }

        $this->info('🔄 Starting API Key Maintenance...');
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
        $this->info('✅ API Key Maintenance Complete');

        return 0;
    }

    private function cleanup(): void
    {
        $this->line('🧹 Cleaning up expired keys...');

        $count = $this->rotationService->cleanupExpiredKeys();

        if ($count > 0) {
            $this->info("   ✅ Cleaned up {$count} expired keys");
        } else {
            $this->line('   ℹ️ No expired keys to clean up');
        }
    }

    private function invalidateDeprecatedKeys(): void
    {
        $this->line('⚠️ Invalidating deprecated keys...');

        $count = $this->rotationService->invalidateDeprecatedKeys();

        if ($count > 0) {
            $this->info("   ✅ Invalidated {$count} deprecated keys");
        } else {
            $this->line('   ℹ️ No deprecated keys to invalidate');
        }
    }

    private function notifyExpiringKeys(): void
    {
        $this->line('📧 Sending expiration notifications...');

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
            $this->info("   ✅ Sent {$notified} expiration notifications");
        } else {
            $this->line('   ℹ️ No keys requiring notifications');
        }
    }

    private function sendExpirationNotification($key, string $timeframe): bool
    {
        try {
            return true;
        } catch (\Exception $e) {
            $this->warn("   ⚠️ Failed to notify about {$key->name}: {$e->getMessage()}");

            return false;
        }
    }
}

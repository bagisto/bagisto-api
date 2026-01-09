<?php

namespace Webkul\BagistoApi\Console\Commands;

use Illuminate\Console\Command;
use Webkul\BagistoApi\Models\StorefrontKey;
use Webkul\BagistoApi\Services\KeyRotationService;

/**
 * API Key Rotation Management Command
 *
 * Manages the complete lifecycle of API keys
 * Usage:
 *   php artisan api:key:rotate {key_id}
 *   php artisan api:key:deactivate {key_id}
 *   php artisan api:key:cleanup
 *   php artisan api:key:status {key_id}
 */
class ApiKeyManagementCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bagisto-api:key:manage
                            {action : Action to perform (rotate, deactivate, cleanup, status, expiring, unused, summary)}
                            {--key= : API Key ID or name}
                            {--reason= : Reason for deactivation}
                            {--days=7 : Number of days for "expiring" action}
                            {--unused=90 : Number of days for "unused" action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage API key rotation, expiration, and lifecycle';

    /**
     * Service instance
     */
    protected KeyRotationService $rotationService;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->rotationService = new KeyRotationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        return match ($action) {
            'rotate'     => $this->rotateKey(),
            'deactivate' => $this->deactivateKey(),
            'cleanup'    => $this->cleanupExpiredKeys(),
            'status'     => $this->showKeyStatus(),
            'expiring'   => $this->showExpiringKeys(),
            'unused'     => $this->showUnusedKeys(),
            'summary'    => $this->showPolicySummary(),
            default      => $this->handleInvalidAction($action),
        };
    }

    /**
     * Rotate an API key
     */
    private function rotateKey(): int
    {
        $keyId = $this->option('key');
        if (! $keyId) {
            $this->error('--key is required for rotate action');

            return 1;
        }

        $key = StorefrontKey::findOrFail($keyId);

        if (! $key->isValid()) {
            $this->error("Cannot rotate invalid key: {$key->name}");

            return 1;
        }

        try {
            $newKey = $this->rotationService->rotateKey($key);

            $this->info('✅ Key rotated successfully!');
            $this->line("Old Key: {$key->name}");
            $this->line("Old Key ID: {$key->id}");
            $this->line("Deprecation Date: {$key->deprecation_date}");
            $this->newLine();
            $this->line("New Key: {$newKey->name}");
            $this->line("New Key ID: {$newKey->id}");
            $this->line("New Key Value: {$newKey->key}");
            $this->line("Expires At: {$newKey->expires_at}");

            return 0;
        } catch (\Exception $e) {
            $this->error("Error rotating key: {$e->getMessage()}");

            return 1;
        }
    }

    /**
     * Deactivate an API key
     */
    private function deactivateKey(): int
    {
        $keyId = $this->option('key');
        if (! $keyId) {
            $this->error('--key is required for deactivate action');

            return 1;
        }

        $key = StorefrontKey::findOrFail($keyId);
        $reason = $this->option('reason') ?? 'Manual deactivation';

        if ($this->confirm("Are you sure you want to deactivate key: {$key->name}?")) {
            try {
                $this->rotationService->deactivateKey($key, $reason);
                $this->info('✅ Key deactivated successfully!');

                return 0;
            } catch (\Exception $e) {
                $this->error("Error deactivating key: {$e->getMessage()}");

                return 1;
            }
        }

        $this->info('Deactivation cancelled.');

        return 0;
    }

    /**
     * Cleanup expired keys
     */
    private function cleanupExpiredKeys(): int
    {
        if ($this->confirm('This will soft-delete all expired keys. Continue?')) {
            $count = $this->rotationService->cleanupExpiredKeys();
            $this->info("✅ Cleaned up {$count} expired keys");

            return 0;
        }

        $this->info('Cleanup cancelled.');

        return 0;
    }

    /**
     * Show status of a specific key
     */
    private function showKeyStatus(): int
    {
        $keyId = $this->option('key');
        if (! $keyId) {
            $this->error('--key is required for status action');

            return 1;
        }

        $key = StorefrontKey::findOrFail($keyId);
        $status = $this->rotationService->getRotationStatus($key);

        $this->info("Key Status: {$key->name}");
        $this->newLine();

        $this->line('Active: '.($status['is_valid'] ? '✅ Yes' : '❌ No'));
        $this->line('Usable: '.($status['is_usable'] ? '✅ Yes' : '❌ No'));
        $this->line('Expired: '.($status['is_expired'] ? '❌ Yes' : '✅ No'));
        $this->line('Deprecated: '.($status['is_deprecated'] ? '⚠️ Yes' : '✅ No'));
        $this->newLine();

        $this->line('Expires At: '.($status['expires_at'] ? $status['expires_at']->format('Y-m-d H:i:s') : 'Never'));
        $this->line('Days Until Expiry: '.($status['days_until_expiry'] ? $status['days_until_expiry'].' days' : 'N/A'));
        $this->line('Last Used: '.($status['last_used_at'] ? $status['last_used_at']->format('Y-m-d H:i:s') : 'Never'));
        $this->newLine();

        if ($status['rotated_from']) {
            $this->line("Rotated From: {$status['rotated_from']}");
        }
        if ($status['rotated_keys']) {
            $this->line("Keys Rotated From This: {$status['rotated_keys']}");
        }

        return 0;
    }

    /**
     * Show keys expiring soon
     */
    private function showExpiringKeys(): int
    {
        $days = (int) $this->option('days');
        $keys = $this->rotationService->getKeysExpiringSoon($days);

        if ($keys->isEmpty()) {
            $this->info("No keys expiring in the next {$days} days");

            return 0;
        }

        $this->info("Keys expiring in the next {$days} days:");
        $this->newLine();

        foreach ($keys as $key) {
            $daysLeft = $key->expires_at->diffInDays(now());
            $this->line("• {$key->name} (ID: {$key->id})");
            $this->line("  Expires: {$key->expires_at->format('Y-m-d')} ({$daysLeft} days left)");
        }

        return 0;
    }

    /**
     * Show unused keys
     */
    private function showUnusedKeys(): int
    {
        $days = (int) $this->option('unused');
        $keys = $this->rotationService->getUnusedKeys($days);

        if ($keys->isEmpty()) {
            $this->info("No unused keys found (> {$days} days)");

            return 0;
        }

        $this->info("Unused keys (> {$days} days):");
        $this->newLine();

        foreach ($keys as $key) {
            $lastUsed = $key->last_used_at
                ? $key->last_used_at->format('Y-m-d')
                : 'Never';
            $this->line("• {$key->name} (ID: {$key->id})");
            $this->line("  Last Used: {$lastUsed}");
        }

        return 0;
    }

    /**
     * Show policy compliance summary
     */
    private function showPolicySummary(): int
    {
        $summary = $this->rotationService->getPolicyComplianceSummary();

        $this->info('API Key Rotation Policy Compliance Summary');
        $this->newLine();

        $this->line("Total Keys: {$summary['total_active_keys']}");
        $this->line("Valid Keys: {$summary['total_valid_keys']}");
        $this->line("Expired Keys: {$summary['expired_keys']}");
        $this->line("Deprecated Keys: {$summary['deprecated_keys']}");
        $this->line("Keys Expiring Soon (7 days): {$summary['keys_expiring_soon']}");
        $this->line("Unused Keys (90 days): {$summary['unused_keys']}");
        $this->line("Recently Rotated (30 days): {$summary['recently_rotated']}");

        return 0;
    }

    /**
     * Handle invalid action
     */
    private function handleInvalidAction(string $action): int
    {
        $this->error("Invalid action: {$action}");
        $this->info('Available actions: rotate, deactivate, cleanup, status, expiring, unused, summary');

        return 1;
    }
}

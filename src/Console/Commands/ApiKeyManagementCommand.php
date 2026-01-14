<?php

namespace Webkul\BagistoApi\Console\Commands;

use Illuminate\Console\Command;
use Webkul\BagistoApi\Models\StorefrontKey;
use Webkul\BagistoApi\Services\KeyRotationService;

/**
 * Manage API key rotation, expiration, and lifecycle.
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
     * Service instance.
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
     * Rotate an API key.
     */
    private function rotateKey(): int
    {
        $keyId = $this->option('key');
        if (! $keyId) {
            $this->error(__('bagistoapi::app.graphql.install.key-management-required', ['action' => 'rotate']));
            return 1;
        }

        try {
            $key = $this->findKey($keyId);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }

        if (! $key->isValid()) {
            $this->error(__('bagistoapi::app.graphql.install.key-rotation-error', ['error' => 'invalid key']));
            return 1;
        }

        try {
            $newKey = $this->rotationService->rotateKey($key);

            $this->info(__('bagistoapi::app.graphql.install.key-rotated-success'));
            $this->line(__('bagistoapi::app.graphql.install.old-key', ['name' => $key->name]));
            $this->line(__('bagistoapi::app.graphql.install.old-key-id', ['id' => $key->id]));
            $this->line(__('bagistoapi::app.graphql.install.deprecation-date', ['date' => $key->deprecation_date]));
            $this->newLine();
            $this->line(__('bagistoapi::app.graphql.install.new-key', ['name' => $newKey->name]));
            $this->line(__('bagistoapi::app.graphql.install.new-key-id', ['id' => $newKey->id]));
            $this->line(__('bagistoapi::app.graphql.install.new-key-value', ['key' => $newKey->key]));
            $this->line(__('bagistoapi::app.graphql.install.expires-at', ['date' => $newKey->expires_at]));

            return 0;
        } catch (\Exception $e) {
            $this->error(__('bagistoapi::app.graphql.install.key-rotation-error', ['error' => $e->getMessage()]));
            return 1;
        }
    }

    /**
     * Deactivate an API key.
     */
    private function deactivateKey(): int
    {
        $keyId = $this->option('key');
        if (! $keyId) {
            $this->error(__('bagistoapi::app.graphql.install.key-management-required', ['action' => 'deactivate']));
            return 1;
        }

        try {
            $key = $this->findKey($keyId);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }

        $reason = $this->option('reason') ?? 'Manual deactivation';

        if ($this->confirm(__('bagistoapi::app.graphql.install.confirm-deactivate', ['name' => $key->name]))) {
            try {
                $this->rotationService->deactivateKey($key, $reason);
                $this->info(__('bagistoapi::app.graphql.install.key-deactivated-success'));
                return 0;
            } catch (\Exception $e) {
                $this->error(__('bagistoapi::app.graphql.install.key-deactivation-error', ['error' => $e->getMessage()]));
                return 1;
            }
        }

        $this->info(__('bagistoapi::app.graphql.install.deactivation-cancelled'));
        return 0;
    }

    /**
     * Clean up expired keys.
     */
    private function cleanupExpiredKeys(): int
    {
        if ($this->confirm(__('bagistoapi::app.graphql.install.confirm-cleanup'))) {
            $count = $this->rotationService->cleanupExpiredKeys();
            $this->info(__('bagistoapi::app.graphql.install.cleanup-success', ['count' => $count]));
            return 0;
        }

        $this->info(__('bagistoapi::app.graphql.install.cleanup-cancelled'));
        return 0;
    }

    /**
     * Show status of a specific key.
     */
    private function showKeyStatus(): int
    {
        $keyId = $this->option('key');
        if (! $keyId) {
            $this->error(__('bagistoapi::app.graphql.install.key-management-required', ['action' => 'status']));
            return 1;
        }

        try {
            $key = $this->findKey($keyId);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }

        $status = $this->rotationService->getRotationStatus($key);

        $this->info(__('bagistoapi::app.graphql.install.key-status-title', ['name' => $key->name]));
        $this->newLine();

        $this->line(__('bagistoapi::app.graphql.install.key-status-active', ['status' => $status['is_valid'] ? '✅ Yes' : '❌ No']));
        $this->line(__('bagistoapi::app.graphql.install.key-status-usable', ['status' => $status['is_usable'] ? '✅ Yes' : '❌ No']));
        $this->line(__('bagistoapi::app.graphql.install.key-status-expired', ['status' => $status['is_expired'] ? '❌ Yes' : '✅ No']));
        $this->line(__('bagistoapi::app.graphql.install.key-status-deprecated', ['status' => $status['is_deprecated'] ? '⚠️ Yes' : '✅ No']));
        $this->newLine();

        $expiresAtText = $status['expires_at'] ? $status['expires_at']->format('Y-m-d H:i:s') : __('bagistoapi::app.graphql.install.key-never');
        $this->line(__('bagistoapi::app.graphql.install.key-status-expires-at', ['expires' => $expiresAtText]));

        $daysText = $status['days_until_expiry'] ? $status['days_until_expiry'].__('bagistoapi::app.graphql.install.key-days') : 'N/A';
        $this->line(__('bagistoapi::app.graphql.install.key-status-days-until-expiry', ['days' => $daysText]));

        $lastUsedText = $status['last_used_at'] ? $status['last_used_at']->format('Y-m-d H:i:s') : __('bagistoapi::app.graphql.install.key-never');
        $this->line(__('bagistoapi::app.graphql.install.key-status-last-used', ['date' => $lastUsedText]));
        $this->newLine();

        if ($status['rotated_from']) {
            $this->line(__('bagistoapi::app.graphql.install.key-status-rotated-from', ['key' => $status['rotated_from']]));
        }
        if ($status['rotated_keys']) {
            $this->line(__('bagistoapi::app.graphql.install.key-status-rotated-keys', ['count' => $status['rotated_keys']]));
        }

        return 0;
    }

    /**
     * Show keys expiring soon.
     */
    private function showExpiringKeys(): int
    {
        $days = (int) $this->option('days');
        $keys = $this->rotationService->getKeysExpiringSoon($days);

        if ($keys->isEmpty()) {
            $this->info(__('bagistoapi::app.graphql.install.no-keys-expiring', ['days' => $days]));
            return 0;
        }

        $this->info(__('bagistoapi::app.graphql.install.keys-expiring-title', ['days' => $days]));
        $this->newLine();

        foreach ($keys as $key) {
            $daysLeft = $key->expires_at->diffInDays(now());
            $this->line(__('bagistoapi::app.graphql.install.key-display-format', ['name' => $key->name, 'id' => $key->id]));
            $this->line(__('bagistoapi::app.graphql.install.key-expires-display', ['date' => $key->expires_at->format('Y-m-d'), 'days' => $daysLeft]));
        }

        return 0;
    }

    /**
     * Show unused keys.
     */
    private function showUnusedKeys(): int
    {
        $days = (int) $this->option('unused');
        $keys = $this->rotationService->getUnusedKeys($days);

        if ($keys->isEmpty()) {
            $this->info(__('bagistoapi::app.graphql.install.no-unused-keys', ['days' => $days]));
            return 0;
        }

        $this->info(__('bagistoapi::app.graphql.install.unused-keys-title', ['days' => $days]));
        $this->newLine();

        foreach ($keys as $key) {
            $lastUsed = $key->last_used_at
                ? $key->last_used_at->format('Y-m-d')
                : __('bagistoapi::app.graphql.install.key-never');
            $this->line(__('bagistoapi::app.graphql.install.key-display-format', ['name' => $key->name, 'id' => $key->id]));
            $this->line(__('bagistoapi::app.graphql.install.key-last-used-display', ['date' => $lastUsed]));
        }

        return 0;
    }

    /**
     * Show policy compliance summary.
     */
    private function showPolicySummary(): int
    {
        $summary = $this->rotationService->getPolicyComplianceSummary();

        $this->info(__('bagistoapi::app.graphql.install.policy-compliance-summary'));
        $this->newLine();

        $this->line(__('bagistoapi::app.graphql.install.total-keys', ['count' => $summary['total_active_keys']]));
        $this->line(__('bagistoapi::app.graphql.install.valid-keys', ['count' => $summary['total_valid_keys']]));
        $this->line(__('bagistoapi::app.graphql.install.expired-keys', ['count' => $summary['expired_keys']]));
        $this->line(__('bagistoapi::app.graphql.install.deprecated-keys', ['count' => $summary['deprecated_keys']]));
        $this->line(__('bagistoapi::app.graphql.install.keys-expiring-soon', ['count' => $summary['keys_expiring_soon']]));
        $this->line(__('bagistoapi::app.graphql.install.unused-keys-summary', ['count' => $summary['unused_keys']]));
        $this->line(__('bagistoapi::app.graphql.install.recently-rotated', ['count' => $summary['recently_rotated']]));

        return 0;
    }

    /**
     * Handle invalid action.
     */
    private function handleInvalidAction(string $action): int
    {
        $this->error(__('bagistoapi::app.graphql.install.invalid-action', ['action' => $action]));
        $this->info(__('bagistoapi::app.graphql.install.available-actions'));

        return 1;
    }

    /**
     * Find a key by ID or name.
     *
     * @param  string  $keyIdentifier
     * @return StorefrontKey
     *
     * @throws \Exception
     */
    private function findKey(string $keyIdentifier): StorefrontKey
    {
        if (is_numeric($keyIdentifier)) {
            $key = StorefrontKey::find($keyIdentifier);
            if ($key) {
                return $key;
            }
        }

        $key = StorefrontKey::where('name', $keyIdentifier)->first();
        if ($key) {
            return $key;
        }

        throw new \Exception(__('bagistoapi::app.graphql.key-not-found', ['identifier' => $keyIdentifier]));
    }
}

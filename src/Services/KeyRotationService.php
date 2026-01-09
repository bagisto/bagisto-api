<?php

namespace Webkul\BagistoApi\Services;

use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Models\StorefrontKey;

/**
 * API Key Rotation Service
 *
 * Manages the complete lifecycle of API keys:
 * - Rotation with transition periods
 * - Expiration enforcement
 * - Deprecation tracking
 * - Usage monitoring
 */
class KeyRotationService
{
    /**
     * Rotate an API key with a transition period
     *
     * The old key will remain valid for a configurable period (default: 7 days)
     * to allow clients time to update their configurations
     *
     * @return StorefrontKey The new key
     */
    public function rotateKey(StorefrontKey $key): StorefrontKey
    {
        if (! $key->isValid()) {
            throw new \Exception('Cannot rotate an invalid or expired key');
        }

        return $key->rotate();
    }

    /**
     * Deactivate an API key immediately
     *
     * Used for security incidents, compromised keys, etc.
     */
    public function deactivateKey(StorefrontKey $key, ?string $reason = null): void
    {
        $key->deactivate($reason);

        Log::warning('API Key Deactivated', [
            'key_id'         => $key->id,
            'key_name'       => $key->name,
            'reason'         => $reason,
            'deactivated_at' => now(),
        ]);
    }

    /**
     * Batch deactivate keys (for compromise scenarios)
     *
     * @return int Number of keys deactivated
     */
    public function deactivateKeysBatch(array $keyIds, ?string $reason = null): int
    {
        $count = StorefrontKey::whereIn('id', $keyIds)
            ->update(['is_active' => false]);

        Log::warning('Batch API Keys Deactivated', [
            'count'          => $count,
            'key_ids'        => $keyIds,
            'reason'         => $reason,
            'deactivated_at' => now(),
        ]);

        return $count;
    }

    /**
     * Clean up expired keys
     *
     * Soft-deletes expired keys for audit trail while removing them from active use
     *
     * @return int Number of keys cleaned up
     */
    public function cleanupExpiredKeys(): int
    {
        $expiredKeys = StorefrontKey::expired()
            ->whereNull('deleted_at')
            ->get();

        $count = 0;
        foreach ($expiredKeys as $key) {
            $key->delete(); // Soft delete
            $count++;
        }

        if ($count > 0) {
            Log::info('Expired API Keys Cleaned Up', [
                'count'      => $count,
                'cleaned_at' => now(),
            ]);
        }

        return $count;
    }

    /**
     * Invalidate deprecated keys that have passed their deprecation date
     *
     * After deprecation period, old keys are automatically disabled
     *
     * @return int Number of keys invalidated
     */
    public function invalidateDeprecatedKeys(): int
    {
        $deprecatedKeys = StorefrontKey::deprecated()
            ->where('is_active', true)
            ->get();

        $count = 0;
        foreach ($deprecatedKeys as $key) {
            $key->deactivate('Auto-deactivated after deprecation period');
            $count++;
        }

        if ($count > 0) {
            Log::info('Deprecated API Keys Invalidated', [
                'count'          => $count,
                'invalidated_at' => now(),
            ]);
        }

        return $count;
    }

    /**
     * Get rotation status for a key
     */
    public function getRotationStatus(StorefrontKey $key): array
    {
        return [
            'is_valid'               => $key->isValid(),
            'is_usable'              => $key->isUsable(),
            'is_expired'             => $key->isExpired(),
            'is_deprecated'          => $key->isDeprecated(),
            'expires_at'             => $key->expires_at,
            'deprecation_date'       => $key->deprecation_date,
            'last_used_at'           => $key->last_used_at,
            'days_until_expiry'      => $key->expires_at ? $key->expires_at->diffInDays(now()) : null,
            'days_until_deprecation' => $key->deprecation_date ? $key->deprecation_date->diffInDays(now()) : null,
            'rotated_from'           => $key->rotatedFromKey ? $key->rotatedFromKey->name : null,
            'rotated_keys'           => $key->rotatedKeys()->count(),
        ];
    }

    /**
     * Get keys expiring soon (within specified days)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getKeysExpiringSoon(int $withinDays = 7)
    {
        return StorefrontKey::where('is_active', true)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($withinDays)])
            ->orderBy('expires_at')
            ->get();
    }

    /**
     * Get keys unused for a long time
     *
     * Identifies potential unused API keys that can be reviewed for removal
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnusedKeys(int $daysUnused = 90)
    {
        return StorefrontKey::where('is_active', true)
            ->where(function ($query) use ($daysUnused) {
                $query->whereNull('last_used_at')
                    ->orWhere('last_used_at', '<', now()->subDays($daysUnused));
            })
            ->orderByDesc('last_used_at')
            ->get();
    }

    /**
     * Get a summary of key rotation policy compliance
     */
    public function getPolicyComplianceSummary(): array
    {
        return [
            'total_active_keys'  => StorefrontKey::active()->count(),
            'total_valid_keys'   => StorefrontKey::valid()->count(),
            'expired_keys'       => StorefrontKey::expired()->count(),
            'deprecated_keys'    => StorefrontKey::deprecated()->count(),
            'keys_expiring_soon' => $this->getKeysExpiringSoon(7)->count(),
            'unused_keys'        => $this->getUnusedKeys(90)->count(),
            'recently_rotated'   => StorefrontKey::whereNotNull('rotated_from_id')
                ->where('created_at', '>', now()->subDays(30))
                ->count(),
        ];
    }
}

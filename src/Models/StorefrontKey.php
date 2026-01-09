<?php

namespace Webkul\BagistoApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * StorefrontKey Model
 *
 * Manages API keys for storefront/shop APIs authentication.
 * Features:
 * - Key generation and validation
 * - Expiration tracking and enforcement
 * - Key rotation with deprecation period
 * - Usage tracking (last used timestamp)
 * - Soft deletes for audit trail
 */
class StorefrontKey extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'key',
        'is_active',
        'rate_limit',
        'allowed_ips',
        'expires_at',
        'last_used_at',
        'deprecation_date',
        'rotated_from_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active'        => 'boolean',
        'rate_limit'       => 'integer',
        'allowed_ips'      => 'json',
        'expires_at'       => 'datetime',
        'last_used_at'     => 'datetime',
        'deprecation_date' => 'datetime',
    ];

    /**
     * Generate a new unique API key with proper prefix.
     */
    public static function generateKey(): string
    {
        $prefix = config('api-platform.storefront_key_prefix', 'pk_storefront_');

        return $prefix.Str::random(32);
    }

    /**
     * Check if key is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if key is in deprecation period (deprecated but still valid)
     * Used during key rotation transition period
     */
    public function isDeprecated(): bool
    {
        return $this->deprecation_date && $this->deprecation_date->isPast() && ! $this->isExpired();
    }

    /**
     * Check if key is completely active and valid
     */
    public function isValid(): bool
    {
        return $this->is_active && ! $this->isExpired() && ! $this->trashed();
    }

    /**
     * Check if key is still usable (includes deprecated keys)
     */
    public function isUsable(): bool
    {
        return $this->is_active && ! $this->isExpired() && ! $this->trashed();
    }

    /**
     * Update the last used timestamp
     */
    public function recordUsage(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Check if key was recently used (within last hour)
     */
    public function wasRecentlyUsed(): bool
    {
        return $this->last_used_at && $this->last_used_at->isAfter(now()->subHour());
    }

    /**
     * Deactivate the key immediately
     *
     * @param  string  $reason  Optional reason for deactivation
     */
    public function deactivate(?string $reason = null): void
    {
        $this->update(['is_active' => false]);

        // Log the deactivation
        \Log::info("API Key deactivated: {$this->name} (ID: {$this->id}). Reason: {$reason}");
    }

    /**
     * Rotate the key by creating a new one and marking this as deprecated
     *
     * @return StorefrontKey The new key
     */
    public function rotate(): self
    {
        // Create new key with same properties
        $newKey = self::create([
            'name'             => $this->name.' (rotated '.now()->format('Y-m-d H:i').')',
            'key'              => self::generateKey(),
            'is_active'        => true,
            'rate_limit'       => $this->rate_limit,
            'allowed_ips'      => $this->allowed_ips,
            'expires_at'       => now()->addMonths(config('api-platform.key_rotation_policy.expiration_months', 12)),
            'deprecation_date' => null,
            'rotated_from_id'  => $this->id,
        ]);

        // Set deprecation date for old key (allow transition period)
        $transitionDays = config('api-platform.key_rotation_policy.transition_days', 7);
        $this->update([
            'deprecation_date' => now()->addDays($transitionDays),
        ]);

        // Log the rotation
        \Log::info("API Key rotated: {$this->name} (ID: {$this->id}). New key ID: {$newKey->id}");

        return $newKey;
    }

    /**
     * Scope query to only active keys
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereNull('deleted_at');
    }

    /**
     * Scope query to only valid (non-expired, active) keys
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereNull('deleted_at');
    }

    /**
     * Scope query to only expired keys
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Scope query to only deprecated keys (in transition period)
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeprecated($query)
    {
        return $query->whereNotNull('deprecation_date')
            ->where('deprecation_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Validate and retrieve a storefront key by key string
     * Checks expiration, activity status, and IP restrictions
     */
    public static function validateKey(string $key, ?string $ipAddress = null): ?self
    {
        $storefront = self::valid()
            ->where('key', $key)
            ->first();

        if (! $storefront) {
            return null;
        }

        // Check IP whitelist if configured
        if ($storefront->allowed_ips && is_array($storefront->allowed_ips)) {
            if (! in_array($ipAddress, $storefront->allowed_ips)) {
                return null;
            }
        }

        // Record usage
        $storefront->recordUsage();

        return $storefront;
    }

    /**
     * Relation: Get the key this was rotated from
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rotatedFromKey()
    {
        return $this->belongsTo(self::class, 'rotated_from_id');
    }

    /**
     * Relation: Get all keys rotated from this key
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rotatedKeys()
    {
        return $this->hasMany(self::class, 'rotated_from_id');
    }
}

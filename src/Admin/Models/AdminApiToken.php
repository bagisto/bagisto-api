<?php

namespace Webkul\BagistoApi\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BagistoApi\Admin\Contracts\AdminApiToken as AdminApiTokenContract;
use Webkul\User\Models\AdminProxy;

class AdminApiToken extends Model implements AdminApiTokenContract
{
    /**
     * The status of a token that can be used.
     */
    public const STATUS_ACTIVE = 'active';

    /**
     * The status of a token past its expiry.
     */
    public const STATUS_EXPIRED = 'expired';

    /**
     * The status of a revoked token.
     */
    public const STATUS_REVOKED = 'revoked';

    /**
     * The table.
     */
    protected $table = 'admin_api_tokens';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'admin_id',
        'created_by_admin_id',
        'name',
        'jti',
        'token_preview',
        'signing_kid',
        'domain',
        'resolved_ips',
        'resolved_at',
        'domain_mismatch_count',
        'last_domain_mismatch_at',
        'expires_at',
        'last_used_at',
        'last_ip_address',
        'last_user_agent',
        'last_channel',
        'revoked_at',
        'revoked_reason',
        'revoked_by_admin_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'resolved_ips' => 'array',
        'resolved_at' => 'datetime',
        'domain_mismatch_count' => 'integer',
        'last_domain_mismatch_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * The admin the token acts as.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminProxy::modelClass(), 'admin_id');
    }

    /**
     * The admin who created the token.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(AdminProxy::modelClass(), 'created_by_admin_id');
    }

    /**
     * The admin who revoked the token.
     */
    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(AdminProxy::modelClass(), 'revoked_by_admin_id');
    }

    /**
     * Whether the token has been revoked.
     */
    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    /**
     * Whether the token is past its expiry; a token without one never expires.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    /**
     * Whether the token is neither revoked nor expired.
     */
    public function isActive(): bool
    {
        return ! $this->isRevoked()
            && ! $this->isExpired();
    }

    /**
     * The token's status: active, expired or revoked.
     */
    public function status(): string
    {
        if ($this->isRevoked()) {
            return self::STATUS_REVOKED;
        }

        return $this->isExpired()
            ? self::STATUS_EXPIRED
            : self::STATUS_ACTIVE;
    }
}

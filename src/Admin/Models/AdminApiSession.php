<?php

namespace Webkul\BagistoApi\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\BagistoApi\Admin\Contracts\AdminApiSession as AdminApiSessionContract;
use Webkul\User\Models\AdminProxy;

class AdminApiSession extends Model implements AdminApiSessionContract
{
    /**
     * The client type sent by a web application.
     */
    public const CLIENT_TYPE_WEB = 'web';

    /**
     * The client type sent by a mobile application.
     */
    public const CLIENT_TYPE_MOBILE = 'mobile';

    /**
     * The client type sent by a point-of-sale application.
     */
    public const CLIENT_TYPE_POS = 'pos';

    /**
     * The client type sent by a desktop application.
     */
    public const CLIENT_TYPE_DESKTOP = 'desktop';

    /**
     * The client type used when none, or an unknown one, is sent.
     */
    public const CLIENT_TYPE_OTHER = 'other';

    /**
     * Every client type a session may be stored with.
     */
    public const CLIENT_TYPES = [
        self::CLIENT_TYPE_WEB,
        self::CLIENT_TYPE_MOBILE,
        self::CLIENT_TYPE_POS,
        self::CLIENT_TYPE_DESKTOP,
        self::CLIENT_TYPE_OTHER,
    ];

    /**
     * The channel of a REST request.
     */
    public const CHANNEL_REST = 'rest';

    /**
     * The channel of a GraphQL request.
     */
    public const CHANNEL_GRAPHQL = 'graphql';

    /**
     * Every channel a request may be recorded with.
     */
    public const CHANNELS = [
        self::CHANNEL_REST,
        self::CHANNEL_GRAPHQL,
    ];

    /**
     * The revoke reason when the admin logs out of the session.
     */
    public const REVOKED_LOGOUT = 'logout';

    /**
     * The revoke reason when the admin logs out of every session.
     */
    public const REVOKED_LOGOUT_ALL = 'logout_all';

    /**
     * The revoke reason when an already used refresh token is presented again.
     */
    public const REVOKED_REFRESH_REUSE = 'refresh_token_reuse';

    /**
     * The revoke reason when the admin's password changes.
     */
    public const REVOKED_PASSWORD_CHANGE = 'password_change';

    /**
     * The revoke reason when the admin resets a forgotten password.
     */
    public const REVOKED_PASSWORD_RESET = 'password_reset';

    /**
     * The revoke reason when the admin is deactivated.
     */
    public const REVOKED_DEACTIVATED = 'deactivated';

    /**
     * The revoke reason when another admin revokes the session.
     */
    public const REVOKED_BY_ADMIN = 'revoked_by_admin';

    /**
     * The table.
     */
    protected $table = 'admin_api_sessions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'admin_id',
        'name',
        'client_type',
        'ip_address',
        'user_agent',
        'last_ip_address',
        'last_user_agent',
        'last_used_at',
        'last_channel',
        'refresh_expires_at',
        'expires_at',
        'revoked_at',
        'revoked_reason',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'last_used_at' => 'datetime',
        'refresh_expires_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * The admin the session belongs to.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminProxy::modelClass(), 'admin_id');
    }

    /**
     * The refresh tokens issued in the session.
     */
    public function refreshTokens(): HasMany
    {
        return $this->hasMany(AdminApiRefreshTokenProxy::modelClass(), 'session_id');
    }

    /**
     * Whether the session has been revoked.
     */
    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    /**
     * Whether the session has reached its absolute lifetime.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    /**
     * Whether the session is neither revoked nor past its absolute lifetime.
     */
    public function isActive(): bool
    {
        return ! $this->isRevoked()
            && ! $this->isExpired();
    }

    /**
     * Store an empty client type as the default one.
     */
    public function setClientTypeAttribute($value): void
    {
        $this->attributes['client_type'] = in_array($value, self::CLIENT_TYPES, true)
            ? $value
            : self::CLIENT_TYPE_OTHER;
    }
}

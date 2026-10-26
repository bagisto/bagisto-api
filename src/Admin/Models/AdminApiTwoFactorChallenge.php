<?php

namespace Webkul\BagistoApi\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BagistoApi\Admin\Contracts\AdminApiTwoFactorChallenge as AdminApiTwoFactorChallengeContract;
use Webkul\User\Models\AdminProxy;

class AdminApiTwoFactorChallenge extends Model implements AdminApiTwoFactorChallengeContract
{
    /**
     * Wrong codes a challenge accepts before it is revoked.
     */
    public const MAX_FAILED_ATTEMPTS = 5;

    /**
     * The table.
     */
    protected $table = 'admin_api_two_factor_challenges';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'admin_id',
        'token',
        'name',
        'client_type',
        'ip_address',
        'user_agent',
        'failed_attempts',
        'expires_at',
        'consumed_at',
        'revoked_at',
    ];

    /**
     * The attributes hidden from serialization.
     */
    protected $hidden = [
        'token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'failed_attempts' => 'integer',
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * The admin who passed the password step.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminProxy::modelClass(), 'admin_id');
    }

    /**
     * Whether the challenge can still be answered: not used, not revoked and not expired.
     */
    public function isPending(): bool
    {
        return $this->consumed_at === null
            && $this->revoked_at === null
            && $this->expires_at !== null
            && $this->expires_at->isFuture();
    }

    /**
     * Store an unknown client type as the default one.
     */
    public function setClientTypeAttribute($value): void
    {
        $this->attributes['client_type'] = in_array($value, AdminApiSession::CLIENT_TYPES, true)
            ? $value
            : AdminApiSession::CLIENT_TYPE_OTHER;
    }
}

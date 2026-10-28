<?php

namespace Webkul\BagistoApi\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\BagistoApi\Contracts\CustomerApiRefreshToken as CustomerApiRefreshTokenContract;

class CustomerApiRefreshToken extends Model implements CustomerApiRefreshTokenContract
{
    /**
     * The name of the "updated at" column, which the table does not have.
     */
    public const UPDATED_AT = null;

    /**
     * The table.
     */
    protected $table = 'customer_api_refresh_tokens';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'session_id',
        'token',
        'expires_at',
        'used_at',
        'replaced_by_id',
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
        'session_id' => 'integer',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * The session the refresh token was issued in.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(CustomerApiSessionProxy::modelClass(), 'session_id');
    }

    /**
     * Whether the refresh token has already been exchanged.
     */
    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    /**
     * Whether the refresh token is past its expiry.
     */
    public function isExpired(): bool
    {
        return $this->expires_at === null
            || $this->expires_at->isPast();
    }
}

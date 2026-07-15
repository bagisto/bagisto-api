<?php

namespace Webkul\BagistoApi\Admin\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Symfony\Component\HttpFoundation\IpUtils;
use Webkul\User\Models\Admin;

class AdminPersonalAccessToken extends Model
{
    protected $table = 'admin_personal_access_tokens';

    protected $fillable = [
        'admin_id',
        'name',
        'description',
        'token',
        'token_preview',
        'permission_type',
        'abilities',
        'rate_limit_per_minute',
        'rate_limit_per_day',
        'allowed_ips',
        'last_used_at',
        'expires_at',
        'status',
        'revoked_at',
        'revoked_by_admin_id',
        'regenerated_at',
        'regenerated_by_admin_id',
        'regenerated_to_id',
        'created_by_admin_id',
    ];

    protected $hidden = [
        'token',
    ];

    protected $casts = [
        'abilities' => 'array',
        'allowed_ips' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'regenerated_at' => 'datetime',
    ];

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REVOKED = 'revoked';

    public const STATUS_REGENERATED = 'regenerated';

    public const PERMISSION_TYPE_ALL = 'all';

    public const PERMISSION_TYPE_CUSTOM = 'custom';

    public const PERMISSION_TYPE_SAME_AS_WEB = 'same_as_web';

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'revoked_by_admin_id');
    }

    public function regeneratedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'regenerated_by_admin_id');
    }

    public function regeneratedTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'regenerated_to_id');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeRevoked($query)
    {
        return $query->where('status', self::STATUS_REVOKED);
    }

    public function scopeRegenerated($query)
    {
        return $query->where('status', self::STATUS_REGENERATED);
    }

    public function scopeListed($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_ACTIVE]);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    public function isRegenerated(): bool
    {
        return $this->status === self::STATUS_REGENERATED;
    }

    public function isUsable(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->token === null) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * NULL or empty allowed_ips means "any IP allowed".
     * 127.0.0.1 always passes for dev convenience (mirrors ApiKeyService::ipAllowed()).
     * Supports IPv4, IPv6, and CIDR notation via Symfony IpUtils.
     */
    public function isIpAllowed(string $ip): bool
    {
        $list = $this->allowed_ips;

        if (empty($list)) {
            return true;
        }

        if ($ip === '127.0.0.1') {
            return true;
        }

        return IpUtils::checkIp($ip, array_values(array_filter((array) $list)));
    }

    public function can(string $ability): bool
    {
        $abilities = $this->resolvedAbilities();

        if (in_array('*', $abilities, true)) {
            return true;
        }

        return in_array($ability, $abilities, true);
    }

    public function resolvedAbilities(): array
    {
        $admin = $this->admin;

        $effective = match ($this->permission_type) {
            // `all` and `same_as_web` both mean "everything the owner's role allows".
            // A role of type `all` => unrestricted (`*`); otherwise the role's own
            // permission list. A token can never exceed its owner's role.
            self::PERMISSION_TYPE_ALL,
            self::PERMISSION_TYPE_SAME_AS_WEB => $admin && $admin->role
                ? ($admin->role->permission_type === 'all'
                    ? ['*']
                    : ($admin->role->permissions ?? []))
                : [],
            self::PERMISSION_TYPE_CUSTOM => $this->abilities ?? [],
            default => [],
        };

        if (! in_array('*', $effective, true) && $admin && $admin->role) {
            if ($admin->role->permission_type !== 'all') {
                $rolePerms = $admin->role->permissions ?? [];
                $effective = array_values(array_intersect($effective, $rolePerms));
            }
        }

        return $effective;
    }

    /**
     * Constrain a resolved admin's in-memory role to THIS token's effective
     * abilities, so every endpoint permission check (which reads
     * $admin->role->permission_type / ->permissions) automatically respects the
     * token's permission_type — `custom` tokens are restricted to their frozen
     * abilities, `same_as_web`/`all` follow the role.
     *
     * Applied once, at admin-resolution time (the AdminApiGuard / AdminAuthHelper
     * chokepoint), so the ~70 existing role-based checks need no change. The role
     * is replaced with a CLONE — the persisted Role row is never mutated.
     */
    public function applyAbilityScope(?Authenticatable $admin): void
    {
        if (! $admin || $admin->getAttribute('__token_ability_scoped')) {
            return;
        }

        // Mark first so this never re-runs (and never feeds its own output back in).
        $admin->setAttribute('__token_ability_scoped', true);

        $abilities = $this->resolvedAbilities();

        // Full access (role is `all`) — leave the role untouched.
        if (in_array('*', $abilities, true)) {
            return;
        }

        $role = $admin->role ?? null;

        if (! $role) {
            return;
        }

        $restricted = clone $role;
        $restricted->permission_type = self::PERMISSION_TYPE_CUSTOM;
        $restricted->setAttribute('permissions', array_values($abilities));

        $admin->setRelation('role', $restricted);
    }
}

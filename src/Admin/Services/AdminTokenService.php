<?php

namespace Webkul\BagistoApi\Admin\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Models\AdminPersonalAccessToken;
use Webkul\User\Models\Admin;

class AdminTokenService
{
    public const DEFAULT_RATE_LIMIT_PER_MINUTE = 60;

    public const DEFAULT_RATE_LIMIT_PER_DAY = 10000;

    public const DEFAULT_VALID_FOR_DAYS = 365;

    public const TOKEN_PREVIEW_LENGTH = 8;

    public function createDraft(array $data, ?int $createdByAdminId = null): AdminPersonalAccessToken
    {
        return AdminPersonalAccessToken::create([
            'admin_id'            => $data['admin_id'],
            'name'                => $data['name'],
            'description'         => $data['description'] ?? null,
            'permission_type'     => $data['permission_type'],
            'abilities'           => $this->normalizeAbilities($data),
            'allowed_ips'         => $this->normalizeAllowedIps($data),
            'status'              => AdminPersonalAccessToken::STATUS_DRAFT,
            'created_by_admin_id' => $createdByAdminId,
        ]);
    }

    public function updateDraftMetadata(AdminPersonalAccessToken $token, array $data): AdminPersonalAccessToken
    {
        $token->update([
            'name'            => $data['name'],
            'description'     => $data['description'] ?? null,
            'permission_type' => $data['permission_type'],
            'abilities'       => $this->normalizeAbilities($data),
            'allowed_ips'     => $this->normalizeAllowedIps($data),
        ]);

        return $token->fresh();
    }

    public function updateActiveMetadata(AdminPersonalAccessToken $token, array $data): AdminPersonalAccessToken
    {
        $token->update([
            'name'                  => $data['name'],
            'description'           => $data['description'] ?? null,
            'permission_type'       => $data['permission_type'],
            'abilities'             => $this->normalizeAbilities($data),
            'allowed_ips'           => $this->normalizeAllowedIps($data),
            'expires_at'            => $this->resolveExpiresAt($data),
            'rate_limit_per_minute' => $this->resolveRateLimit($data, 'rate_limit_per_minute', 'rate_min_mode'),
            'rate_limit_per_day'    => $this->resolveRateLimit($data, 'rate_limit_per_day', 'rate_day_mode'),
        ]);

        return $token->fresh();
    }

    public function generate(AdminPersonalAccessToken $token, array $overrides = []): array
    {
        if ($token->status !== AdminPersonalAccessToken::STATUS_DRAFT) {
            throw new \DomainException('Only draft tokens can be generated.');
        }

        $plain = $this->makePlainText();

        $expiresAt = $this->resolveGenerateExpiresAt($overrides);
        $rateMin = $this->resolveGenerateRateLimit($overrides, 'rate_limit_per_minute', 'rate_min_mode', self::DEFAULT_RATE_LIMIT_PER_MINUTE);
        $rateDay = $this->resolveGenerateRateLimit($overrides, 'rate_limit_per_day', 'rate_day_mode', self::DEFAULT_RATE_LIMIT_PER_DAY);
        $allowedIps = $this->resolveGenerateAllowedIps($overrides, $token);

        $token->update([
            'token'                 => hash('sha256', $plain),
            'token_preview'         => substr($plain, 0, self::TOKEN_PREVIEW_LENGTH),
            'status'                => AdminPersonalAccessToken::STATUS_ACTIVE,
            'expires_at'            => $expiresAt,
            'rate_limit_per_minute' => $rateMin,
            'rate_limit_per_day'    => $rateDay,
            'allowed_ips'           => $allowedIps,
        ]);

        return [
            'token'      => $token->fresh(),
            'plain_text' => $this->prefixedPlainText($token->id, $plain),
        ];
    }

    /**
     * For Generate: NULL only when user explicitly picks "Never expires".
     * If mode is "expires" but no date provided, fall back to today + 1 year default.
     */
    protected function resolveGenerateExpiresAt(array $overrides): ?Carbon
    {
        if (! array_key_exists('expires_mode', $overrides)) {
            return Carbon::now()->addDays(self::DEFAULT_VALID_FOR_DAYS);
        }

        if (($overrides['expires_mode'] ?? null) === 'never') {
            return null;
        }

        if (! empty($overrides['expires_at'])) {
            return Carbon::parse($overrides['expires_at']);
        }

        return Carbon::now()->addDays(self::DEFAULT_VALID_FOR_DAYS);
    }

    /**
     * For Generate: NULL only when user explicitly picks "Unlimited".
     * If mode is "limited" but no value provided, fall back to default.
     */
    protected function resolveGenerateRateLimit(array $overrides, string $valueKey, string $modeKey, int $default): ?int
    {
        if (! array_key_exists($modeKey, $overrides)) {
            return $default;
        }

        if (($overrides[$modeKey] ?? null) === 'unlimited') {
            return null;
        }

        $value = $overrides[$valueKey] ?? null;

        if ($value === null || $value === '') {
            return $default;
        }

        return (int) $value;
    }

    /**
     * For Generate: resolve allowed_ips from the form overrides.
     * - ip_mode=any (or omitted with no list)        → NULL (no IP restriction)
     * - ip_mode=restricted + allowed_ips_text/array  → cleaned array
     * - omitted from overrides entirely              → preserve existing draft value
     */
    protected function resolveGenerateAllowedIps(array $overrides, AdminPersonalAccessToken $token): ?array
    {
        $mode = $overrides['ip_mode'] ?? null;

        if ($mode === 'any') {
            return null;
        }

        $hasArray = array_key_exists('allowed_ips', $overrides);
        $hasText = array_key_exists('allowed_ips_text', $overrides);

        if (! $hasArray && ! $hasText && $mode === null) {
            return $token->allowed_ips;
        }

        if ($hasArray) {
            return $this->normalizeAllowedIps(['allowed_ips' => $overrides['allowed_ips']]);
        }

        if ($hasText) {
            return $this->normalizeAllowedIps(['allowed_ips' => $overrides['allowed_ips_text']]);
        }

        return null;
    }

    public function regenerate(AdminPersonalAccessToken $oldToken, int $regeneratedByAdminId): array
    {
        return DB::transaction(function () use ($oldToken, $regeneratedByAdminId) {
            $newToken = AdminPersonalAccessToken::create([
                'admin_id'              => $oldToken->admin_id,
                'name'                  => $oldToken->name,
                'description'           => $oldToken->description,
                'permission_type'       => $oldToken->permission_type,
                'abilities'             => $oldToken->abilities,
                'rate_limit_per_minute' => $oldToken->rate_limit_per_minute,
                'rate_limit_per_day'    => $oldToken->rate_limit_per_day,
                'allowed_ips'           => $oldToken->allowed_ips,
                'expires_at'            => $oldToken->expires_at,
                'status'                => AdminPersonalAccessToken::STATUS_DRAFT,
                'created_by_admin_id'   => $regeneratedByAdminId,
            ]);

            $generated = $this->generate($newToken);

            $oldToken->update([
                'token'                   => null,
                'status'                  => AdminPersonalAccessToken::STATUS_REGENERATED,
                'regenerated_at'          => now(),
                'regenerated_by_admin_id' => $regeneratedByAdminId,
                'regenerated_to_id'       => $generated['token']->id,
            ]);

            return $generated;
        });
    }

    public function revoke(AdminPersonalAccessToken $token, int $revokedByAdminId): AdminPersonalAccessToken
    {
        $token->update([
            'token'               => null,
            'status'              => AdminPersonalAccessToken::STATUS_REVOKED,
            'revoked_at'          => now(),
            'revoked_by_admin_id' => $revokedByAdminId,
        ]);

        return $token->fresh();
    }

    public function adminsWithoutActiveToken()
    {
        $busyAdminIds = AdminPersonalAccessToken::listed()->pluck('admin_id')->all();

        return Admin::whereNotIn('id', $busyAdminIds)
            ->orderBy('name')
            ->get();
    }

    public function maskedPreview(AdminPersonalAccessToken $token): string
    {
        if ($token->token_preview === null) {
            return '—';
        }

        return $token->id.'|'.$token->token_preview.'...xxxx';
    }

    /**
     * Normalise allowed_ips coming in from either form requests (already an
     * array of entries) or the Blade form (textarea text — one entry per line).
     *
     * Returns NULL when the caller did not include the key at all (preserve
     * existing value semantics for partial updates is handled by the request),
     * or when the resulting list is empty (= no IP restriction).
     */
    protected function normalizeAllowedIps(array $data): ?array
    {
        if (! array_key_exists('allowed_ips', $data)) {
            return null;
        }

        $value = $data['allowed_ips'];

        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $entries = preg_split('/[\r\n,]+/', $value) ?: [];
        } elseif (is_array($value)) {
            $entries = $value;
        } else {
            return null;
        }

        $cleaned = array_values(array_unique(array_filter(array_map(
            fn ($v) => trim((string) $v),
            $entries,
        ))));

        return $cleaned === [] ? null : $cleaned;
    }

    protected function normalizeAbilities(array $data): array
    {
        $type = $data['permission_type'] ?? AdminPersonalAccessToken::PERMISSION_TYPE_CUSTOM;

        if ($type === AdminPersonalAccessToken::PERMISSION_TYPE_ALL) {
            return ['*'];
        }

        if ($type === AdminPersonalAccessToken::PERMISSION_TYPE_SAME_AS_WEB) {
            return [];
        }

        $abilities = $data['abilities'] ?? $data['permissions'] ?? [];

        if (! is_array($abilities)) {
            $abilities = [];
        }

        return array_values(array_unique(array_filter(array_map('strval', $abilities))));
    }

    protected function resolveRateLimit(array $data, string $valueKey, string $modeKey): ?int
    {
        $mode = $data[$modeKey] ?? 'limited';

        if ($mode === 'unlimited') {
            return null;
        }

        $value = $data[$valueKey] ?? null;

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function resolveExpiresAt(array $data): ?Carbon
    {
        $mode = $data['expires_mode'] ?? 'expires';

        if ($mode === 'never') {
            return null;
        }

        $value = $data['expires_at'] ?? null;

        if (empty($value)) {
            return null;
        }

        return Carbon::parse($value);
    }

    protected function makePlainText(): string
    {
        return Str::random(40);
    }

    protected function prefixedPlainText(int $id, string $plain): string
    {
        return $id.'|'.$plain;
    }
}

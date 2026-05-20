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

        $token->update([
            'token'                 => hash('sha256', $plain),
            'token_preview'         => substr($plain, 0, self::TOKEN_PREVIEW_LENGTH),
            'status'                => AdminPersonalAccessToken::STATUS_ACTIVE,
            'expires_at'            => $expiresAt,
            'rate_limit_per_minute' => $rateMin,
            'rate_limit_per_day'    => $rateDay,
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

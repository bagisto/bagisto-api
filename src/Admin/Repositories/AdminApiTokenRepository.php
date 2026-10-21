<?php

namespace Webkul\BagistoApi\Admin\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Webkul\BagistoApi\Admin\Contracts\AdminApiToken as AdminApiTokenContract;
use Webkul\BagistoApi\Admin\Models\AdminApiToken;
use Webkul\Core\Eloquent\Repository;

class AdminApiTokenRepository extends Repository
{
    /**
     * Specify the model contract.
     */
    public function model(): string
    {
        return AdminApiTokenContract::class;
    }

    /**
     * Find an API token by its JWT id, with its admin and the admin's role loaded.
     */
    public function findByJti(string $jti): ?AdminApiToken
    {
        return $this->model->newQuery()
            ->with('admin.role')
            ->where('jti', $jti)
            ->first();
    }

    /**
     * Find an API token with its admin, creator and revoker loaded.
     */
    public function findWithAdmin(int $id): ?AdminApiToken
    {
        return $this->model->newQuery()
            ->with(['admin.role', 'createdBy', 'revokedBy'])
            ->find($id);
    }

    /**
     * A page of API tokens, newest first, optionally of one admin, one status and one domain.
     */
    public function paginateFiltered(?int $adminId, ?string $status, ?string $domain, int $page, int $perPage): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['admin', 'createdBy'])
            ->when($adminId !== null, fn ($query) => $query->where('admin_id', $adminId))
            ->when($domain !== null, fn ($query) => $query->where('domain', $domain))
            ->when($status === AdminApiToken::STATUS_REVOKED, fn ($query) => $query->whereNotNull('revoked_at'))
            ->when($status === AdminApiToken::STATUS_EXPIRED, fn ($query) => $query->whereNull('revoked_at')
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()))
            ->when($status === AdminApiToken::STATUS_ACTIVE, fn ($query) => $query->whereNull('revoked_at')
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                }))
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Change the name of an API token.
     */
    public function rename(AdminApiToken $token, string $name): AdminApiToken
    {
        $token->forceFill(['name' => $name])->save();

        return $token;
    }

    /**
     * Revoke an API token that is not revoked yet, keeping the first reason it was revoked for.
     */
    public function revoke(AdminApiToken $token, string $reason, ?int $revokedByAdminId = null): AdminApiToken
    {
        if ($token->isRevoked()) {
            return $token;
        }

        $token->forceFill([
            'revoked_at' => now(),
            'revoked_reason' => $reason,
            'revoked_by_admin_id' => $revokedByAdminId,
        ])->save();

        return $token;
    }

    /**
     * The API tokens that are neither revoked nor expired.
     */
    public function active(): Collection
    {
        return $this->activeQuery()->with('admin')->orderBy('id')->get();
    }

    /**
     * The active API tokens signed by any key other than the given one.
     */
    public function activeNotSignedWith(string $kid): Collection
    {
        return $this->activeQuery()
            ->with('admin')
            ->where(function ($query) use ($kid) {
                $query->whereNull('signing_kid')
                    ->orWhere('signing_kid', '!=', $kid);
            })
            ->orderBy('id')
            ->get();
    }

    /**
     * The key ids that still sign at least one active API token.
     */
    public function signingKidsInUse(): array
    {
        return $this->activeQuery()
            ->whereNotNull('signing_kid')
            ->distinct()
            ->pluck('signing_kid')
            ->all();
    }

    /**
     * Store the string and key of an API token that was signed again.
     */
    public function markReissued(AdminApiToken $token, string $kid, string $preview): AdminApiToken
    {
        $token->forceFill([
            'signing_kid' => $kid,
            'token_preview' => $preview,
        ])->save();

        return $token;
    }

    /**
     * Record where and how an API token was last used.
     */
    public function touchUsage(AdminApiToken $token, array $usage): AdminApiToken
    {
        $token->forceFill($usage)->save();

        return $token;
    }

    /**
     * Cache the addresses the token's domain resolved to.
     */
    public function storeResolvedIps(AdminApiToken $token, array $addresses): AdminApiToken
    {
        $token->forceFill([
            'resolved_ips' => array_values($addresses),
            'resolved_at' => now(),
        ])->save();

        return $token;
    }

    /**
     * Count one more request made with the token from a domain it is not bound to.
     */
    public function recordDomainMismatch(AdminApiToken $token): AdminApiToken
    {
        $token->forceFill([
            'domain_mismatch_count' => (int) $token->domain_mismatch_count + 1,
            'last_domain_mismatch_at' => now(),
        ])->save();

        return $token;
    }

    /**
     * Revoke every API token of an admin that is not revoked yet, and return how many were revoked.
     */
    public function revokeAllForAdmin(int $adminId, string $reason, ?int $revokedByAdminId = null): int
    {
        return $this->model->newQuery()
            ->where('admin_id', $adminId)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
                'revoked_reason' => $reason,
                'revoked_by_admin_id' => $revokedByAdminId,
                'updated_at' => now(),
            ]);
    }

    /**
     * The query over API tokens that are neither revoked nor expired.
     */
    protected function activeQuery()
    {
        return $this->model->newQuery()
            ->whereNull('revoked_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }
}

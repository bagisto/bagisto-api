<?php

namespace Webkul\BagistoApi\Admin\Repositories;

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

<?php

namespace Webkul\BagistoApi\Admin\Repositories;

use Carbon\CarbonInterface;
use Closure;
use Webkul\BagistoApi\Admin\Contracts\AdminApiRefreshToken as AdminApiRefreshTokenContract;
use Webkul\BagistoApi\Admin\Models\AdminApiRefreshToken;
use Webkul\Core\Eloquent\Repository;

class AdminApiRefreshTokenRepository extends Repository
{
    /**
     * Specify the model contract.
     */
    public function model(): string
    {
        return AdminApiRefreshTokenContract::class;
    }

    /**
     * Find a refresh token with its session, the session's admin and the admin's role loaded.
     */
    public function findWithSession(int $id): ?AdminApiRefreshToken
    {
        return $this->model->newQuery()
            ->with('session.admin.role')
            ->find($id);
    }

    /**
     * Run a callback in one database transaction, so a failure part way leaves nothing behind.
     */
    public function transaction(Closure $callback): mixed
    {
        return $this->model->getConnection()->transaction($callback);
    }

    /**
     * Mark an unused refresh token as used, returning false when another request already used it.
     */
    public function claim(int $id): bool
    {
        return $this->model->newQuery()
            ->whereKey($id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]) === 1;
    }

    /**
     * Link a used refresh token to the one issued in its place.
     */
    public function markReplacedBy(int $id, int $replacedById): void
    {
        $this->model->newQuery()
            ->whereKey($id)
            ->update(['replaced_by_id' => $replacedById]);
    }

    /**
     * Delete the refresh tokens past their expiry, used or not, and return how many were deleted.
     */
    public function deleteExpiredBefore(CarbonInterface $before): int
    {
        return $this->model->newQuery()
            ->where('expires_at', '<', $before)
            ->delete();
    }
}

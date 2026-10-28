<?php

namespace Webkul\BagistoApi\Admin\Repositories;

use Carbon\CarbonInterface;
use Webkul\BagistoApi\Admin\Contracts\AdminApiTwoFactorChallenge as AdminApiTwoFactorChallengeContract;
use Webkul\BagistoApi\Admin\Models\AdminApiTwoFactorChallenge;
use Webkul\Core\Eloquent\Repository;

class AdminApiTwoFactorChallengeRepository extends Repository
{
    /**
     * Specify the model contract.
     */
    public function model(): string
    {
        return AdminApiTwoFactorChallengeContract::class;
    }

    /**
     * Find a challenge with its admin and the admin's role loaded.
     */
    public function findWithAdmin(int $id): ?AdminApiTwoFactorChallenge
    {
        return $this->model->newQuery()
            ->with('admin.role')
            ->find($id);
    }

    /**
     * Mark a pending challenge as used, returning false when another request already used or revoked it.
     */
    public function consume(int $id): bool
    {
        return $this->pendingQuery($id)->update([
            'consumed_at' => now(),
            'updated_at' => now(),
        ]) === 1;
    }

    /**
     * Count one wrong code against a pending challenge, revoking it on the last allowed attempt.
     */
    public function recordFailure(int $id): AdminApiTwoFactorChallenge
    {
        $this->pendingQuery($id)->increment('failed_attempts', 1, ['updated_at' => now()]);

        $this->pendingQuery($id)
            ->where('failed_attempts', '>=', AdminApiTwoFactorChallenge::MAX_FAILED_ATTEMPTS)
            ->update([
                'revoked_at' => now(),
                'updated_at' => now(),
            ]);

        return $this->model->newQuery()->findOrFail($id);
    }

    /**
     * Revoke every pending challenge of an admin and return how many were revoked.
     */
    public function revokePendingForAdmin(int $adminId): int
    {
        return $this->model->newQuery()
            ->where('admin_id', $adminId)
            ->whereNull('consumed_at')
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * Delete the challenges that expired before the given moment, answered or not.
     */
    public function deleteExpiredBefore(CarbonInterface $before): int
    {
        return $this->model->newQuery()
            ->where('expires_at', '<', $before)
            ->delete();
    }

    /**
     * The query over one challenge while it is neither used nor revoked nor expired.
     */
    protected function pendingQuery(int $id)
    {
        return $this->model->newQuery()
            ->whereKey($id)
            ->whereNull('consumed_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now());
    }
}

<?php

namespace Webkul\BagistoApi\Admin\Repositories;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Webkul\BagistoApi\Admin\Contracts\AdminApiSession as AdminApiSessionContract;
use Webkul\BagistoApi\Admin\Models\AdminApiSession;
use Webkul\Core\Eloquent\Repository;

class AdminApiSessionRepository extends Repository
{
    /**
     * Specify the model contract.
     */
    public function model(): string
    {
        return AdminApiSessionContract::class;
    }

    /**
     * Find a session with its admin and the admin's role loaded.
     */
    public function findWithAdmin(int $id): ?AdminApiSession
    {
        return $this->model->newQuery()
            ->with('admin.role')
            ->find($id);
    }

    /**
     * The sessions of an admin that are neither revoked nor past their absolute lifetime.
     */
    public function activeForAdmin(int $adminId): Collection
    {
        return $this->model->newQuery()
            ->where('admin_id', $adminId)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Revoke a session that is not revoked yet, keeping the first reason it was revoked for.
     */
    public function revoke(AdminApiSession $session, string $reason): AdminApiSession
    {
        if ($session->isRevoked()) {
            return $session;
        }

        $session->forceFill([
            'revoked_at' => now(),
            'revoked_reason' => $reason,
        ])->save();

        return $session;
    }

    /**
     * Revoke every live session of an admin, optionally keeping one, and return how many were revoked.
     */
    public function revokeAllForAdmin(int $adminId, string $reason, ?int $exceptSessionId = null): int
    {
        return $this->model->newQuery()
            ->where('admin_id', $adminId)
            ->whereNull('revoked_at')
            ->when($exceptSessionId !== null, fn ($query) => $query->whereKeyNot($exceptSessionId))
            ->update([
                'revoked_at' => now(),
                'revoked_reason' => $reason,
                'updated_at' => now(),
            ]);
    }

    /**
     * Record where and how a session was last used.
     */
    public function touchUsage(AdminApiSession $session, array $usage): AdminApiSession
    {
        $session->forceFill($usage)->save();

        return $session;
    }

    /**
     * Delete the sessions revoked, or expired, before the given moment, with their refresh tokens.
     */
    public function deleteEndedBefore(CarbonInterface $before): int
    {
        return $this->model->newQuery()
            ->where(function ($query) use ($before) {
                $query->where('revoked_at', '<', $before)
                    ->orWhere('expires_at', '<', $before)
                    ->orWhere('refresh_expires_at', '<', $before);
            })
            ->delete();
    }
}

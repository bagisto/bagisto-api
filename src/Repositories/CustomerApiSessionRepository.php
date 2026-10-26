<?php

namespace Webkul\BagistoApi\Repositories;

use Carbon\CarbonInterface;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Webkul\BagistoApi\Contracts\CustomerApiSession as CustomerApiSessionContract;
use Webkul\BagistoApi\Models\CustomerApiSession;
use Webkul\Core\Eloquent\Repository;

class CustomerApiSessionRepository extends Repository
{
    /**
     * Specify the model contract.
     */
    public function model(): string
    {
        return CustomerApiSessionContract::class;
    }

    /**
     * Run a callback in one database transaction, so a failure part way leaves nothing behind.
     */
    public function transaction(Closure $callback): mixed
    {
        return $this->model->getConnection()->transaction($callback);
    }

    /**
     * Find a session with its customer loaded.
     */
    public function findWithCustomer(int $id): ?CustomerApiSession
    {
        return $this->model->newQuery()
            ->with('customer')
            ->find($id);
    }

    /**
     * The live sessions of a customer, newest first.
     */
    public function activeForCustomer(int $customerId): Collection
    {
        return $this->activeQuery()
            ->where('customer_id', $customerId)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * One live session of a customer, or null when the customer has no live session with this id.
     */
    public function findActiveForCustomer(int $customerId, int $id): ?CustomerApiSession
    {
        return $this->activeQuery()
            ->with('customer')
            ->where('customer_id', $customerId)
            ->whereKey($id)
            ->first();
    }

    /**
     * A page of live sessions, newest first, optionally of one customer, client type and channel.
     */
    public function paginateActive(?int $customerId, ?string $clientType, ?int $channelId, int $page, int $perPage): LengthAwarePaginator
    {
        return $this->activeQuery()
            ->with('customer')
            ->when($customerId !== null, fn ($query) => $query->where('customer_id', $customerId))
            ->when($clientType !== null, fn ($query) => $query->where('client_type', $clientType))
            ->when($channelId !== null, fn ($query) => $query->where('channel_id', $channelId))
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * The ids of a customer's live sessions beyond the newest ones to keep, oldest first.
     *
     * @return array<int, int>
     */
    public function idsBeyondNewest(int $customerId, int $keep): array
    {
        return $this->activeQuery()
            ->where('customer_id', $customerId)
            ->orderByDesc('id')
            ->pluck('id')
            ->slice($keep)
            ->reverse()
            ->values()
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Revoke a session that is not revoked yet, keeping the first reason it was revoked for.
     */
    public function revoke(CustomerApiSession $session, string $reason): CustomerApiSession
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
     * Revoke the live sessions with the given ids and return how many were revoked.
     *
     * @param  array<int, int>  $ids
     */
    public function revokeMany(array $ids, string $reason): int
    {
        if ($ids === []) {
            return 0;
        }

        return $this->model->newQuery()
            ->whereKey($ids)
            ->whereNull('revoked_at')
            ->update([
                'revoked_at' => now(),
                'revoked_reason' => $reason,
                'updated_at' => now(),
            ]);
    }

    /**
     * Revoke every live session of a customer, optionally keeping one, and return how many were revoked.
     */
    public function revokeAllForCustomer(int $customerId, string $reason, ?int $exceptSessionId = null): int
    {
        return $this->model->newQuery()
            ->where('customer_id', $customerId)
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
    public function touchUsage(CustomerApiSession $session, array $usage): CustomerApiSession
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

    /**
     * The query over sessions that are neither revoked, idle past their refresh lifetime nor past their absolute lifetime.
     */
    protected function activeQuery(): Builder
    {
        return $this->model->newQuery()
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->where(fn ($query) => $query->whereNull('refresh_expires_at')
                ->orWhere('refresh_expires_at', '>', now()));
    }
}

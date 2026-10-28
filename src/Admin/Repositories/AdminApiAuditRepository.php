<?php

namespace Webkul\BagistoApi\Admin\Repositories;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Webkul\BagistoApi\Admin\Contracts\AdminApiAudit as AdminApiAuditContract;
use Webkul\BagistoApi\Admin\Models\AdminApiAudit;
use Webkul\Core\Eloquent\Repository;

class AdminApiAuditRepository extends Repository
{
    /**
     * Specify the model contract.
     */
    public function model(): string
    {
        return AdminApiAuditContract::class;
    }

    /**
     * Write one audit row.
     */
    public function record(array $attributes): AdminApiAudit
    {
        return $this->model->newQuery()->create($attributes);
    }

    /**
     * The version the next audit row of a record gets, starting at 1.
     */
    public function nextVersion(?string $auditableType, mixed $auditableId): int
    {
        if (
            ! $auditableType
            || $auditableId === null
        ) {
            return 1;
        }

        return (int) $this->model->newQuery()
            ->where('auditable_type', $auditableType)
            ->where('auditable_id', $auditableId)
            ->max('version_id') + 1;
    }

    /**
     * The other audit rows written by the same request as the given row, oldest first.
     */
    public function siblingsOf(AdminApiAudit $audit): Collection
    {
        return $this->model->newQuery()
            ->where('history_id', $audit->history_id)
            ->whereKeyNot($audit->id)
            ->orderBy('id')
            ->get();
    }

    /**
     * Every audit row of the record the given row audits, newest version first.
     */
    public function versionsOf(AdminApiAudit $audit): Collection
    {
        if (
            ! $audit->auditable_type
            || ! $audit->auditable_id
        ) {
            return new Collection;
        }

        return $this->model->newQuery()
            ->where('auditable_type', $audit->auditable_type)
            ->where('auditable_id', $audit->auditable_id)
            ->orderByDesc('version_id')
            ->get();
    }

    /**
     * Delete the audit rows with the given ids and return how many were deleted.
     */
    public function deleteMany(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        return $this->model->newQuery()
            ->whereKey($ids)
            ->delete();
    }

    /**
     * Delete the audit rows written before the cutoff and return how many were deleted.
     */
    public function deleteOlderThan(CarbonInterface $cutoff): int
    {
        return $this->model->newQuery()
            ->where('created_at', '<', $cutoff)
            ->delete();
    }
}

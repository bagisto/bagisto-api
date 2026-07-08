<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminRmaReason;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

class AdminRmaReasonCollectionProvider extends AbstractAdminCollectionProvider
{
    use ChecksAdminPermission;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): \ApiPlatform\Laravel\Eloquent\Paginator
    {
        $this->authorizedAdmin('sales.rma.reasons');

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getSortable(): array
    {
        return ['id', 'position', 'title'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('rma_reasons')->select(
            'rma_reasons.id',
            'rma_reasons.title',
            'rma_reasons.status',
            'rma_reasons.position',
            'rma_reasons.is_admin',
            'rma_reasons.created_at',
            'rma_reasons.updated_at',
        );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['title'])) {
            $query->where('rma_reasons.title', 'like', '%'.$args['title'].'%');
        }

        if (isset($args['status']) && $args['status'] !== '' && $args['status'] !== null) {
            $query->where('rma_reasons.status', (int) $args['status']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $map = ['id' => 'rma_reasons.id', 'position' => 'rma_reasons.position', 'title' => 'rma_reasons.title'];

        $query->orderBy($map[$column] ?? 'rma_reasons.id', $direction);
    }

    protected function mapRow(object $row): AdminRmaReason
    {
        $dto = new AdminRmaReason;
        $dto->id = (int) $row->id;
        $dto->title = $row->title;
        $dto->status = $row->status !== null ? (int) $row->status : null;
        $dto->position = $row->position !== null ? (int) $row->position : null;
        $dto->isAdmin = $row->is_admin !== null ? (int) $row->is_admin : null;
        $dto->resolutionType = DB::table('rma_reason_resolutions')->where('rma_reason_id', $row->id)->pluck('resolution_type')->all();
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}

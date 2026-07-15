<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminRmaReason;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

class AdminRmaReasonItemProvider extends AbstractAdminItemProvider
{
    use ChecksAdminPermission;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $this->authorizedAdmin('sales.rma.reasons');

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.rma.reason-not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return DB::table('rma_reasons')->where('id', $id)->first();
    }

    protected function mapToDto(object $entity): AdminRmaReason
    {
        $dto = new AdminRmaReason;
        $dto->id = (int) $entity->id;
        $dto->title = $entity->title;
        $dto->status = $entity->status !== null ? (int) $entity->status : null;
        $dto->position = $entity->position !== null ? (int) $entity->position : null;
        $dto->isAdmin = $entity->is_admin !== null ? (int) $entity->is_admin : null;
        $dto->resolutionType = DB::table('rma_reason_resolutions')->where('rma_reason_id', $entity->id)->pluck('resolution_type')->all();
        $dto->createdAt = $entity->created_at ? Carbon::parse($entity->created_at)->toIso8601String() : null;
        $dto->updatedAt = $entity->updated_at ? Carbon::parse($entity->updated_at)->toIso8601String() : null;

        return $dto;
    }
}

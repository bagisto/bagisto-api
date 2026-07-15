<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminRmaStatus;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

class AdminRmaStatusItemProvider extends AbstractAdminItemProvider
{
    use ChecksAdminPermission;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $this->authorizedAdmin('sales.rma.statuses');

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.rma.status-not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return DB::table('rma_statuses')->where('id', $id)->first();
    }

    protected function mapToDto(object $entity): AdminRmaStatus
    {
        return self::toDto($entity);
    }

    public static function toDto(object $row): AdminRmaStatus
    {
        $dto = new AdminRmaStatus;
        $dto->id = (int) $row->id;
        $dto->title = $row->title;
        $dto->status = $row->status !== null ? (int) $row->status : null;
        $dto->color = $row->color;
        $dto->default = $row->default !== null ? (int) $row->default : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}

<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminRmaRule;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

class AdminRmaRuleItemProvider extends AbstractAdminItemProvider
{
    use ChecksAdminPermission;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $this->authorizedAdmin('sales.rma.rules');

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.rma.rule-not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return DB::table('rma_rules')->where('id', $id)->first();
    }

    protected function mapToDto(object $entity): AdminRmaRule
    {
        return self::toDto($entity);
    }

    public static function toDto(object $row): AdminRmaRule
    {
        $dto = new AdminRmaRule;
        $dto->id = (int) $row->id;
        $dto->name = $row->name;
        $dto->description = $row->description;
        $dto->status = $row->status !== null ? (int) $row->status : null;
        $dto->returnPeriod = $row->return_period !== null ? (int) $row->return_period : null;
        $dto->default = $row->default !== null ? (int) $row->default : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}

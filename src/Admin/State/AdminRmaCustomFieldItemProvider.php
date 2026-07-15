<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminRmaCustomField;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

class AdminRmaCustomFieldItemProvider extends AbstractAdminItemProvider
{
    use ChecksAdminPermission;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $this->authorizedAdmin('sales.rma.custom-fields');

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.rma.custom-field-not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return DB::table('rma_custom_fields')->where('id', $id)->first();
    }

    protected function mapToDto(object $entity): AdminRmaCustomField
    {
        return self::toDto($entity, true);
    }

    public static function toDto(object $row, bool $withOptions = true): AdminRmaCustomField
    {
        $dto = new AdminRmaCustomField;
        $dto->id = (int) $row->id;
        $dto->code = $row->code;
        $dto->label = $row->label;
        $dto->type = $row->type;
        $dto->isRequired = $row->is_required !== null ? (int) $row->is_required : null;
        $dto->position = $row->position !== null ? (int) $row->position : null;
        $dto->inputValidation = $row->input_validation;
        $dto->status = $row->status !== null ? (int) $row->status : null;
        $dto->options = $withOptions
            ? DB::table('rma_custom_field_options')->where('rma_custom_field_id', $row->id)
                ->get(['id', 'name', 'value'])
                ->map(fn ($o) => ['id' => (int) $o->id, 'name' => $o->name, 'value' => $o->value])->all()
            : [];
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}

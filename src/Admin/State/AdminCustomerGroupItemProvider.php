<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminCustomerGroup;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Customer\Models\CustomerGroup;

class AdminCustomerGroupItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.customer.group.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return CustomerGroup::find($id);
    }

    public function mapToDto(object $entity): object
    {
        return $this->doMap($entity);
    }

    protected function doMap(CustomerGroup $g): AdminCustomerGroup
    {
        $dto = new AdminCustomerGroup;
        $dto->id = (int) $g->id;
        $dto->code = $g->code;
        $dto->name = $g->name;
        $dto->isUserDefined = $g->is_user_defined !== null ? (int) $g->is_user_defined : null;

        // Detail-only
        $dto->customersCount = (int) $g->customers()->count();
        $dto->createdAt = $g->created_at?->toIso8601String();
        $dto->updatedAt = $g->updated_at?->toIso8601String();

        return $dto;
    }
}

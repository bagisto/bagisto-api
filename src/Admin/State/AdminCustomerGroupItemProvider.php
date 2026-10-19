<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminCustomerGroup;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Customer\Models\CustomerGroup;

class AdminCustomerGroupItemProvider extends AbstractAdminItemProvider
{
    /**
     * Map to dto.
     */
    public function mapToDto(object $entity): object
    {
        return $this->doMap($entity);
    }

    /**
     * Get not found lang key.
     */
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.customer.group.not-found';
    }

    /**
     * Find entity.
     */
    protected function findEntity(int $id): ?object
    {
        return CustomerGroup::find($id);
    }

    /**
     * Do map.
     */
    protected function doMap(CustomerGroup $customerGroup): AdminCustomerGroup
    {
        $dto = new AdminCustomerGroup;
        $dto->id = (int) $customerGroup->id;
        $dto->code = $customerGroup->code;
        $dto->name = $customerGroup->name;
        $dto->isUserDefined = $customerGroup->is_user_defined !== null ? (int) $customerGroup->is_user_defined : null;

        $dto->customersCount = (int) $customerGroup->customers()->count();
        $dto->createdAt = $customerGroup->created_at?->toIso8601String();
        $dto->updatedAt = $customerGroup->updated_at?->toIso8601String();

        return $dto;
    }
}

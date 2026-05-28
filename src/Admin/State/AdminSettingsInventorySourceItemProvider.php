<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminSettingsInventorySource;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Inventory\Models\InventorySource;

class AdminSettingsInventorySourceItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.settings.inventory-source.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return InventorySource::find($id);
    }

    protected function mapToDto(object $source): AdminSettingsInventorySource
    {
        /** @var InventorySource $source */
        $dto = new AdminSettingsInventorySource;

        $dto->id = (int) $source->id;
        $dto->code = $source->code;
        $dto->name = $source->name;
        $dto->description = $source->description;
        $dto->contactName = $source->contact_name;
        $dto->contactEmail = $source->contact_email;
        $dto->contactNumber = $source->contact_number;
        $dto->contactFax = $source->contact_fax;
        $dto->country = $source->country;
        $dto->state = $source->state;
        $dto->city = $source->city;
        $dto->street = $source->street;
        $dto->postcode = $source->postcode;
        $dto->priority = $source->priority !== null ? (int) $source->priority : null;
        $dto->latitude = $source->latitude !== null ? (float) $source->latitude : null;
        $dto->longitude = $source->longitude !== null ? (float) $source->longitude : null;
        $dto->status = $source->status !== null ? (int) $source->status : null;
        $dto->createdAt = $source->created_at?->toIso8601String();
        $dto->updatedAt = $source->updated_at?->toIso8601String();

        return $dto;
    }
}

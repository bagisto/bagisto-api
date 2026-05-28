<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminSettingsTaxRate;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Tax\Models\TaxRate;

class AdminSettingsTaxRateItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.settings.tax-rate.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return TaxRate::find($id);
    }

    protected function mapToDto(object $taxRate): AdminSettingsTaxRate
    {
        /** @var TaxRate $taxRate */
        $dto = new AdminSettingsTaxRate;
        $dto->id = (int) $taxRate->id;
        $dto->identifier = $taxRate->identifier;
        $dto->isZip = (bool) $taxRate->is_zip;
        $dto->zipCode = $taxRate->zip_code;
        $dto->zipFrom = $taxRate->zip_from;
        $dto->zipTo = $taxRate->zip_to;
        $dto->state = $taxRate->state;
        $dto->country = $taxRate->country;
        $dto->taxRate = $taxRate->tax_rate !== null ? (float) $taxRate->tax_rate : null;
        $dto->createdAt = $taxRate->created_at?->toIso8601String();
        $dto->updatedAt = $taxRate->updated_at?->toIso8601String();

        return $dto;
    }
}

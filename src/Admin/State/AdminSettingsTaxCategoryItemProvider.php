<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminSettingsTaxCategory;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Tax\Models\TaxCategory;

class AdminSettingsTaxCategoryItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.settings.tax-category.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return TaxCategory::with('tax_rates')->find($id);
    }

    protected function mapToDto(object $taxCategory): AdminSettingsTaxCategory
    {
        /** @var TaxCategory $taxCategory */
        $dto = new AdminSettingsTaxCategory;

        $dto->id = (int) $taxCategory->id;
        $dto->code = $taxCategory->code;
        $dto->name = $taxCategory->name;
        $dto->description = $taxCategory->description;
        $dto->createdAt = $taxCategory->created_at?->toIso8601String();
        $dto->updatedAt = $taxCategory->updated_at?->toIso8601String();

        $dto->taxRates = $taxCategory->tax_rates->map(fn ($rate) => [
            'id'         => (int) $rate->id,
            'identifier' => $rate->identifier,
            'taxRate'    => $rate->tax_rate !== null ? (float) $rate->tax_rate : null,
        ])->all();

        return $dto;
    }

    /**
     * Public alias used by the processor to reuse the mapping logic.
     */
    public function mapToDtoPublic(object $taxCategory): AdminSettingsTaxCategory
    {
        return $this->mapToDto($taxCategory);
    }
}

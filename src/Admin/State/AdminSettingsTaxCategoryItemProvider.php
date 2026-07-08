<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Dto\AdminSettingsTaxCategoryRestDto;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminSettingsTaxCategory;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Tax\Models\TaxCategory;

/**
 * Tax category detail — GET /api/admin/settings/tax-categories/{id} +
 * adminSettingsTaxCategory query.
 *
 * Branches: GraphQL → the AdminSettingsTaxCategory Eloquent model (taxRates
 * resolves as a connection); REST → the flat AdminSettingsTaxCategoryRestDto.
 */
class AdminSettingsTaxCategoryItemProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminSettingsTaxCategory|AdminSettingsTaxCategoryRestDto
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $id = (int) basename((string) ($uriVariables['id'] ?? $context['args']['id'] ?? 0));

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.tax-category.not-found'));
        }

        if (! empty($context['graphql_operation_name'])) {
            $model = AdminSettingsTaxCategory::with('tax_rates')->find($id);

            if (! $model) {
                throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.tax-category.not-found'));
            }

            return $model;
        }

        $taxCategory = TaxCategory::with('tax_rates')->find($id);

        if (! $taxCategory) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.settings.tax-category.not-found'));
        }

        return $this->buildRestDto($taxCategory);
    }

    /**
     * Public alias used by the processor to reuse the REST mapping logic.
     */
    public function buildRestDtoPublic(object $taxCategory): AdminSettingsTaxCategoryRestDto
    {
        return $this->buildRestDto($taxCategory);
    }

    protected function buildRestDto(object $taxCategory): AdminSettingsTaxCategoryRestDto
    {
        /** @var TaxCategory $taxCategory */
        $dto = new AdminSettingsTaxCategoryRestDto;

        $dto->id = (int) $taxCategory->id;
        $dto->code = $taxCategory->code;
        $dto->name = $taxCategory->name;
        $dto->description = $taxCategory->description;
        $dto->createdAt = $taxCategory->created_at?->toIso8601String();
        $dto->updatedAt = $taxCategory->updated_at?->toIso8601String();

        $dto->taxRates = $taxCategory->tax_rates->map(fn ($rate) => [
            'id' => (int) $rate->id,
            'identifier' => $rate->identifier,
            'taxRate' => $rate->tax_rate !== null ? (float) $rate->tax_rate : null,
        ])->all();

        return $dto;
    }
}

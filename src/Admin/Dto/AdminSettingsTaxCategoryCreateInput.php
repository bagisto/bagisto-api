<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for POST /api/admin/settings/tax-categories.
 *
 * Mirrors Bagisto core TaxCategoryController::store validation:
 *   - code: required, unique on tax_categories.code
 *   - name: required
 *   - description: required
 *   - taxrates: required, array of tax_rate ids
 */
class AdminSettingsTaxCategoryCreateInput
{
    #[ApiProperty(description: 'Unique short code identifying the tax category.')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty(description: 'Display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Long-form description.')]
    #[Groups(['mutation'])]
    public ?string $description = null;

    /**
     * @var int[]|null
     */
    #[ApiProperty(description: 'Array of tax_rate ids to attach via the tax_categories_tax_rates pivot.')]
    #[Groups(['mutation'])]
    public ?array $taxrates = null;
}

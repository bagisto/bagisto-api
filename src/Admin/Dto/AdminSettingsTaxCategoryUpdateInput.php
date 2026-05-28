<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/settings/tax-categories/{id} and the delete mutation.
 *
 * Mirrors Bagisto core TaxCategoryController::update — code uniqueness excludes
 * the current id. taxrates re-syncs the pivot (attach/detach as needed).
 */
class AdminSettingsTaxCategoryUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/tax-categories/5). Used by GraphQL mutations.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

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
    #[ApiProperty(description: 'Array of tax_rate ids to sync via the tax_categories_tax_rates pivot.')]
    #[Groups(['mutation'])]
    public ?array $taxrates = null;
}

<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsTaxCategoryCreateInput
{
    /**
     * The code.
     */
    #[ApiProperty(description: 'Unique short code identifying the tax category.')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * The name.
     */
    #[ApiProperty(description: 'Display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The description.
     */
    #[ApiProperty(description: 'Long-form description.')]
    #[Groups(['mutation'])]
    public ?string $description = null;

    /**
     * The ids of the tax rates attached to the tax category.
     *
     * @var int[]|null
     */
    #[ApiProperty(description: 'Array of tax_rate ids to attach via the tax_categories_tax_rates pivot.')]
    #[Groups(['mutation'])]
    public ?array $taxrates = null;
}

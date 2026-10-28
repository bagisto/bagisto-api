<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCatalogProductCreateInput
{
    /**
     * The sku.
     */
    #[ApiProperty(description: 'Unique product SKU (lowercase letters/digits, hyphen-separated).')]
    #[Groups(['mutation'])]
    public ?string $sku = null;

    /**
     * The attribute family id.
     */
    #[ApiProperty(description: 'Attribute family ID the new product belongs to.')]
    #[Groups(['mutation'])]
    public ?int $attributeFamilyId = null;

    /**
     * The type.
     */
    #[ApiProperty(description: 'Product type. Defaults to "simple". One of: simple, virtual, downloadable, grouped, bundle, configurable, booking.')]
    #[Groups(['mutation'])]
    public ?string $type = 'simple';

    /**
     * Required for type='configurable'. Map of attribute_code (or attribute_id)
     * to a non-empty list of option_ids. Example: { "color": [1, 2], "size": [4, 5] }.
     *
     * @var array<string|int, int[]>|null
     */
    #[ApiProperty(description: 'Required for type=configurable. Map of attribute code (or id) to option_ids, e.g. { "color": [1,2], "size": [4,5] }. Generates the Cartesian-product of variants on the server.')]
    #[Groups(['mutation'])]
    public ?array $superAttributes = null;
}

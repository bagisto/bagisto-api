<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCatalogProductCustomerGroupPriceCreateInput
{
    /**
     * The product id.
     */
    #[ApiProperty(description: 'Product ID (forwarded by GraphQL mutations).')]
    #[Groups(['mutation'])]
    public ?int $productId = null;

    /**
     * The qty.
     */
    #[ApiProperty(description: 'Minimum quantity that triggers this tier (>= 1).')]
    #[Groups(['mutation'])]
    public ?int $qty = null;

    /**
     * The value type.
     */
    #[ApiProperty(description: 'fixed (flat price) or discount (percent off base price).')]
    #[Groups(['mutation'])]
    public ?string $value_type = null;

    /**
     * The value.
     */
    #[ApiProperty(description: 'Numeric price (fixed) or percent (discount).')]
    #[Groups(['mutation'])]
    public ?float $value = null;

    /**
     * The customer group id.
     */
    #[ApiProperty(description: 'Customer group ID. null = applies to all groups.')]
    #[Groups(['mutation'])]
    public ?int $customer_group_id = null;
}

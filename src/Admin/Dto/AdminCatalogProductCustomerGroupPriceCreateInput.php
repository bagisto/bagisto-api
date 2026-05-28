<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for creating a product customer-group price row.
 *
 * Used by:
 *   POST /api/admin/catalog/products/{productId}/customer-group-prices
 *   createAdminCatalogProductCustomerGroupPrice GraphQL mutation
 *
 * customer_group_id = null means "applies to all customer groups".
 */
class AdminCatalogProductCustomerGroupPriceCreateInput
{
    #[ApiProperty(description: 'Product ID (forwarded by GraphQL mutations).')]
    #[Groups(['mutation'])]
    public ?int $productId = null;

    #[ApiProperty(description: 'Minimum quantity that triggers this tier (>= 1).')]
    #[Groups(['mutation'])]
    public ?int $qty = null;

    #[ApiProperty(description: 'fixed (flat price) or discount (percent off base price).')]
    #[Groups(['mutation'])]
    public ?string $value_type = null;

    #[ApiProperty(description: 'Numeric price (fixed) or percent (discount).')]
    #[Groups(['mutation'])]
    public ?float $value = null;

    #[ApiProperty(description: 'Customer group ID. null = applies to all groups.')]
    #[Groups(['mutation'])]
    public ?int $customer_group_id = null;
}

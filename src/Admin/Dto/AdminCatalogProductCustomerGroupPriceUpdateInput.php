<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for updating a product customer-group price row.
 *
 * Used by:
 *   PUT    /api/admin/catalog/products/{productId}/customer-group-prices/{id}
 *   DELETE /api/admin/catalog/products/{productId}/customer-group-prices/{id}
 *   updateAdminCatalogProductCustomerGroupPrice GraphQL mutation
 *   deleteAdminCatalogProductCustomerGroupPrice GraphQL mutation
 */
class AdminCatalogProductCustomerGroupPriceUpdateInput
{
    #[ApiProperty(description: 'Product ID (forwarded by GraphQL mutations).')]
    #[Groups(['mutation'])]
    public ?int $productId = null;

    #[ApiProperty(description: 'Customer-group-price row ID (forwarded by GraphQL mutations).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

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

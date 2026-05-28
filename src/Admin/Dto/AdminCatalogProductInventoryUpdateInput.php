<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for bulk-updating a product's per-source inventory quantities.
 *
 * REST   : PUT  /api/admin/catalog/products/{productId}/inventories
 *          { "inventories": { "<source_id>": <qty>, ... } }
 *
 * GraphQL: mutation updateAdminCatalogProductInventories(
 *            input: { productId: Int!, inventories: Iterable! }
 *          )
 *
 * `inventories` is a map of `inventory_source_id => qty`. Quantities must be
 * non-negative integers — qty=0 zeroes-out that source, sources omitted from
 * the map are left untouched.
 */
class AdminCatalogProductInventoryUpdateInput
{
    #[ApiProperty(description: 'Product ID — taken from the URL on REST, required on GraphQL.')]
    #[Groups(['mutation'])]
    public ?int $productId = null;

    /**
     * @var array<int|string,int>|null
     */
    #[ApiProperty(description: 'Map of inventory_source_id → quantity. Use 0 to zero-out a source.')]
    #[Groups(['mutation'])]
    public ?array $inventories = null;

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function setProductId(?int $v): void
    {
        $this->productId = $v;
    }

    public function getInventories(): ?array
    {
        return $this->inventories;
    }

    public function setInventories(?array $v): void
    {
        $this->inventories = $v;
    }
}

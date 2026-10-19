<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCatalogProductInventoryUpdateInput
{
    /**
     * The product id.
     */
    #[ApiProperty(description: 'Product ID — taken from the URL on REST, required on GraphQL.')]
    #[Groups(['mutation'])]
    public ?int $productId = null;

    /**
     * @var array<int|string,int>|null
     */
    #[ApiProperty(description: 'Map of inventory_source_id → quantity. Use 0 to zero-out a source.')]
    #[Groups(['mutation'])]
    public ?array $inventories = null;

    /**
     * Get product id.
     */
    public function getProductId(): ?int
    {
        return $this->productId;
    }

    /**
     * Set product id.
     */
    public function setProductId(?int $value): void
    {
        $this->productId = $value;
    }

    /**
     * Get inventories.
     */
    public function getInventories(): ?array
    {
        return $this->inventories;
    }

    /**
     * Set inventories.
     */
    public function setInventories(?array $value): void
    {
        $this->inventories = $value;
    }
}

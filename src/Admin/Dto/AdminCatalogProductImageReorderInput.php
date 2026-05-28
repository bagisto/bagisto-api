<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * GraphQL/REST input for the reorder operation on AdminCatalogProductImage.
 *
 * Body shape:
 * {
 *   "order": [
 *     {"id": 12, "position": 1},
 *     {"id": 9,  "position": 2}
 *   ]
 * }
 *
 * For GraphQL, the operation also carries `productId` so the processor can scope
 * the operation to the parent product.
 */
class AdminCatalogProductImageReorderInput
{
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $productId = null;

    /**
     * Order rows.
     *
     * @var array<int, array{id: int, position: int}>|null
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?array $order = null;
}

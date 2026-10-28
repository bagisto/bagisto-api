<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * Input for reordering product videos: `{ "order": [{ "id": 8, "position": 1 }] }`.
 */
class AdminCatalogProductVideoReorderInput
{
    #[ApiProperty(description: 'Parent product id.')]
    #[Groups(['mutation'])]
    public ?int $productId = null;

    /**
     * @var array<int, array{id: int, position: int}>|null
     */
    #[ApiProperty(description: 'Video id and position rows.')]
    #[Groups(['mutation'])]
    public ?array $order = null;
}

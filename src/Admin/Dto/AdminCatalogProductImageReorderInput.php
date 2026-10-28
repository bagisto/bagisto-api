<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCatalogProductImageReorderInput
{
    /**
     * The product id.
     */
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

<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCatalogProductImageDeleteInput
{
    /**
     * The product id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $productId = null;

    /**
     * The image id.
     */
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $imageId = null;
}

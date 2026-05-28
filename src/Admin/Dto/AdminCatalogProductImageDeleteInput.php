<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * GraphQL input for deleting a single product image.
 *
 * For REST, the path itself carries the parent productId + image id, so no DTO
 * is needed. GraphQL needs both ids on the mutation input.
 */
class AdminCatalogProductImageDeleteInput
{
    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $productId = null;

    #[ApiProperty]
    #[Groups(['mutation'])]
    public ?int $imageId = null;
}

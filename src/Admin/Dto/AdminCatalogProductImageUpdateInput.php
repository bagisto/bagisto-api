<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/catalog/products/{productId}/images/{id}
 * and updateAltTextAdminCatalogProductImage.
 *
 * The binary is set once at upload; this edits what is stored alongside it.
 */
class AdminCatalogProductImageUpdateInput
{
    #[ApiProperty(description: 'Resource IRI. Used by the GraphQL mutation.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'Parent product id.')]
    #[Groups(['mutation'])]
    public ?int $product_id = null;

    #[ApiProperty(description: 'Image id.')]
    #[Groups(['mutation'])]
    public ?int $image_id = null;

    #[ApiProperty(description: 'Alt text for the image, written to every locale the request covers. Send an empty string to clear it.')]
    #[Groups(['mutation'])]
    public ?string $alt_text = null;

    #[ApiProperty(description: 'Position among the product\'s images.')]
    #[Groups(['mutation'])]
    public ?int $position = null;
}

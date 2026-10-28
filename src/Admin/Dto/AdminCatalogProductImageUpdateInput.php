<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminCatalogProductImageUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI. Used by the GraphQL mutation.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The product id.
     */
    #[ApiProperty(description: 'Parent product id.')]
    #[Groups(['mutation'])]
    public ?int $product_id = null;

    /**
     * The image id.
     */
    #[ApiProperty(description: 'Image id.')]
    #[Groups(['mutation'])]
    public ?int $image_id = null;

    /**
     * The alt text.
     */
    #[ApiProperty(description: 'Alt text for the image, written to every locale the request covers. Send an empty string to clear it.')]
    #[Groups(['mutation'])]
    public ?string $alt_text = null;

    /**
     * The position.
     */
    #[ApiProperty(description: 'Position among the product\'s images.')]
    #[Groups(['mutation'])]
    public ?int $position = null;
}

<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

class AdminCatalogProductVideoDeleteInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Video IRI (delete). GraphQL delete requires the id.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The product id.
     */
    #[ApiProperty(description: 'Parent product id.')]
    #[Groups(['mutation'])]
    public ?int $productId = null;

    /**
     * The video id.
     */
    #[ApiProperty(description: 'Video id.')]
    #[Groups(['mutation'])]
    public ?int $videoId = null;
}

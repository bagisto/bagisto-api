<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Attribute\Groups;

class AdminCatalogProductVideoDeleteInput
{
    #[ApiProperty(description: 'Video IRI (delete). GraphQL delete requires the id.')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'Parent product id.')]
    #[Groups(['mutation'])]
    public ?int $productId = null;

    #[ApiProperty(description: 'Video id.')]
    #[Groups(['mutation'])]
    public ?int $videoId = null;
}

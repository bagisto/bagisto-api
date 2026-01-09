<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class SubscribeToNewsletterInput
{
    /**
     * ID field (optional, for GraphQL API Platform compatibility)
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation', 'query'])]
    public ?string $id = null;

    #[Groups(['mutation'])]
    public string $customerEmail;
}

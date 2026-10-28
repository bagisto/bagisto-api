<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CreateGdprRequestInput
{
    /**
     * The type.
     */
    #[ApiProperty(description: 'The request type. Either "delete" or "update".')]
    #[Groups(['mutation'])]
    public ?string $type = null;

    /**
     * The message.
     */
    #[ApiProperty(description: 'The reason or message for the request.')]
    #[Groups(['mutation'])]
    public ?string $message = null;
}

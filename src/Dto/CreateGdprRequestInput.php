<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class CreateGdprRequestInput
{
    #[ApiProperty(description: 'The request type. Either "delete" or "update".')]
    #[Groups(['mutation'])]
    public ?string $type = null;

    #[ApiProperty(description: 'The reason or message for the request.')]
    #[Groups(['mutation'])]
    public ?string $message = null;
}

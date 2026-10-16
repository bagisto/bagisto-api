<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class UnsubscribeFromNewsletterInput
{
    #[ApiProperty(description: 'Opaque unsubscribe token from the newsletter email.')]
    #[Groups(['mutation'])]
    public ?string $token = null;
}

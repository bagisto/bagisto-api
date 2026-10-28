<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class SubscribeToNewsletterOutput
{
    /**
     * The success.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public bool $success;

    /**
     * The message.
     */
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public string $message;
}

<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * DTO for Contact Us form submission response
 */
class ContactUsOutput
{
    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public bool $success;

    #[Groups(['query', 'mutation'])]
    #[ApiProperty(readable: true, writable: false)]
    public string $message;
}

<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * DTO for Contact Us form submission via GraphQL mutation and REST API
 */
class ContactUsInput
{
    #[ApiProperty(required: false)]
    #[Groups(['mutation', 'query'])]
    public ?string $id = null;

    #[Groups(['mutation'])]
    public string $name;

    #[Groups(['mutation'])]
    public string $email;

    #[Groups(['mutation'])]
    public ?string $contact = null;

    #[Groups(['mutation'])]
    public string $message;
}

<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class ContactUsInput
{
    /**
     * The id.
     */
    #[ApiProperty(required: false)]
    #[Groups(['mutation', 'query'])]
    public ?string $id = null;

    /**
     * The name.
     */
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The email.
     */
    #[Groups(['mutation'])]
    public ?string $email = null;

    /**
     * The contact.
     */
    #[Groups(['mutation'])]
    public ?string $contact = null;

    /**
     * The message.
     */
    #[Groups(['mutation'])]
    public ?string $message = null;
}

<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * DTO for customer login via GraphQL mutation input
 * This explicitly defines all input fields for the GraphQL schema
 * Note: No 'id' field here - only email and password are input
 */
class LoginInput
{
    #[Groups(['mutation'])]
    public string $email;

    #[Groups(['mutation'])]
    public string $password;

    #[Groups(['mutation'])]
    public ?string $deviceToken = null;

    public function __construct(
        string $email = '',
        string $password = '',
        ?string $deviceToken = null,
    ) {
        $this->email = $email;
        $this->password = $password;
        $this->deviceToken = $deviceToken;
    }
}

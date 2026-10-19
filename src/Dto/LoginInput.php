<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class LoginInput
{
    /**
     * The email.
     */
    #[Groups(['mutation'])]
    public string $email;

    /**
     * The password.
     */
    #[Groups(['mutation'])]
    public string $password;

    /**
     * The device token.
     */
    #[Groups(['mutation'])]
    public ?string $deviceToken = null;

    /**
     * Create a new instance.
     */
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

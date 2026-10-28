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
     * A name for the device, shown in the customer's session list.
     */
    #[Groups(['mutation'])]
    public ?string $deviceName = null;

    /**
     * The kind of client: web, mobile, pos, desktop or other.
     */
    #[Groups(['mutation'])]
    public ?string $clientType = null;

    /**
     * The guest cart token of a cart to merge into the customer's cart on login.
     */
    #[Groups(['mutation'])]
    public ?string $guestCartToken = null;

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

<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class ResetPasswordInput
{
    #[Groups(['mutation'])]
    public ?string $token = null;

    #[Groups(['mutation'])]
    public ?string $email = null;

    #[Groups(['mutation'])]
    public ?string $password = null;

    #[Groups(['mutation'])]
    public ?string $password_confirmation = null;
}

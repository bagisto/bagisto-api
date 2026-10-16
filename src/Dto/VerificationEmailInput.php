<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class VerificationEmailInput
{
    #[Groups(['mutation'])]
    public ?string $email = null;
}

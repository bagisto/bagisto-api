<?php

namespace Webkul\BagistoApi\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

class VerifyAccountInput
{
    #[Groups(['mutation'])]
    public ?string $token = null;
}

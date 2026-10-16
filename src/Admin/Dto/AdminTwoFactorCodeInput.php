<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input carrying a two-factor code for enable / disable.
 */
class AdminTwoFactorCodeInput
{
    #[ApiProperty(description: 'Six-digit code from the authenticator app (disable also accepts a backup code).')]
    #[Groups(['mutation'])]
    public ?string $code = null;
}

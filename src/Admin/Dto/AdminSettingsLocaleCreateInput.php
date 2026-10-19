<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsLocaleCreateInput
{
    /**
     * The code.
     */
    #[ApiProperty(description: 'Locale code (e.g. en, fr). Lowercase letters/digits, unique.')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    /**
     * The name.
     */
    #[ApiProperty(description: 'Locale display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    /**
     * The direction.
     */
    #[ApiProperty(description: 'Text direction: ltr or rtl.')]
    #[Groups(['mutation'])]
    public ?string $direction = null;

    /**
     * The logo path.
     */
    #[ApiProperty(description: 'Logo path string (image upload deferred in v1).')]
    #[Groups(['mutation'])]
    public ?string $logo_path = null;
}

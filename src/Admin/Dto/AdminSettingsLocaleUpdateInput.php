<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

class AdminSettingsLocaleUpdateInput
{
    /**
     * The id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/locales/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    /**
     * The code.
     */
    #[ApiProperty(description: 'Locale code (unique excluding self).')]
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
    #[ApiProperty(description: 'Logo path string.')]
    #[Groups(['mutation'])]
    public ?string $logo_path = null;
}

<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Input DTO for PUT /api/admin/settings/locales/{id} and the GraphQL
 * update/delete mutations (delete reuses the same input — only `id` is required).
 */
class AdminSettingsLocaleUpdateInput
{
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/locales/4).')]
    #[Groups(['mutation'])]
    public ?string $id = null;

    #[ApiProperty(description: 'Locale code (unique excluding self).')]
    #[Groups(['mutation'])]
    public ?string $code = null;

    #[ApiProperty(description: 'Locale display name.')]
    #[Groups(['mutation'])]
    public ?string $name = null;

    #[ApiProperty(description: 'Text direction: ltr or rtl.')]
    #[Groups(['mutation'])]
    public ?string $direction = null;

    #[ApiProperty(description: 'Logo path string.')]
    #[Groups(['mutation'])]
    public ?string $logo_path = null;
}

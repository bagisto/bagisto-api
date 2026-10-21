<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;

class AdminSettingsApiTokenRevokeInput
{
    /**
     * The API token, as an IRI or a numeric id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/settings/api-tokens/4) or numeric id.')]
    public ?string $id = null;
}

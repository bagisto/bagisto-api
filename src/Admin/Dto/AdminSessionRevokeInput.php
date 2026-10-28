<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;

class AdminSessionRevokeInput
{
    /**
     * The session or admin user, as an IRI or a numeric id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/admin/auth/sessions/12) or numeric id.')]
    public ?string $id = null;
}

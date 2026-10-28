<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

class CustomerSessionRevokeInput
{
    /**
     * The session, as an IRI or a numeric id.
     */
    #[ApiProperty(description: 'Resource IRI (e.g. /api/shop/customer/sessions/41) or numeric id.')]
    public ?string $id = null;
}

<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

class RevokeGdprRequestInput
{
    #[ApiProperty(description: 'The ID of the GDPR data request to revoke')]
    public ?string $id = null;
}

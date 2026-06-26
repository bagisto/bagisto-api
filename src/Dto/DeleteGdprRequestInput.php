<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;

class DeleteGdprRequestInput
{
    #[ApiProperty(description: 'The ID of the GDPR data request to delete')]
    public ?string $id = null;
}

<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

class AdminTokenRefreshInput
{
    use AcceptsCamelCaseWrites;

    /**
     * The refresh token issued by the last login or refresh.
     */
    #[ApiProperty(description: 'The refresh token from the last login or refresh.', example: '77|3f9c0d...')]
    public ?string $refresh_token = null;
}

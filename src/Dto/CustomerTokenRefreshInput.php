<?php

namespace Webkul\BagistoApi\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

class CustomerTokenRefreshInput
{
    use AcceptsCamelCaseWrites;

    /**
     * The refresh token issued by the last login or refresh.
     */
    #[ApiProperty(description: 'The refresh token from the last login or refresh.', example: '88|7c1f4e2a9b...')]
    public ?string $refresh_token = null;
}

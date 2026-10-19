<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;

class AdminSettingsUserDeleteSelfInput
{
    /**
     * The password.
     */
    #[ApiProperty(description: 'The calling admin\'s current password, for confirmation.')]
    public ?string $password = null;
}

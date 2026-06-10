<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;

/**
 * Input for the admin self-delete action.
 */
class AdminSettingsUserDeleteSelfInput
{
    #[ApiProperty(description: 'The calling admin\'s current password, for confirmation.')]
    public ?string $password = null;
}

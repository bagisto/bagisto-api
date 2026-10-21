<?php

namespace Webkul\BagistoApi\Admin\Dto;

use ApiPlatform\Metadata\ApiProperty;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

class AdminLoginInput
{
    use AcceptsCamelCaseWrites;

    /**
     * The admin's email address.
     */
    #[ApiProperty(description: 'Admin login email.', example: 'admin@example.com')]
    public ?string $email = null;

    /**
     * The admin's password.
     */
    #[ApiProperty(description: 'Admin password.', example: 'admin123')]
    public ?string $password = null;

    /**
     * A label for the device or app, shown in the sessions list.
     */
    #[ApiProperty(description: 'Device or app label shown in the sessions list (max 100).', example: 'iPhone')]
    public ?string $device_name = null;

    /**
     * The kind of client logging in.
     */
    #[ApiProperty(description: 'One of web, mobile, pos, desktop or other (default other).', example: 'mobile')]
    public ?string $client_type = null;
}

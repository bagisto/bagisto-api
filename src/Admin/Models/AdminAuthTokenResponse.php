<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

abstract class AdminAuthTokenResponse
{
    use AcceptsCamelCaseWrites;

    /**
     * The token type to send in the Authorization header.
     */
    #[ApiProperty(writable: false, example: 'Bearer')]
    public ?string $token_type = null;

    /**
     * The signed JWT access token sent as the Bearer token on every request.
     */
    #[ApiProperty(writable: false, example: 'eyJhbGciOiJFUzI1NiIsImtpZCI6Ii4uLiIsInR5cCI6IkpXVCJ9.eyJ...')]
    public ?string $access_token = null;

    /**
     * Seconds until the access token expires.
     */
    #[ApiProperty(writable: false, example: 900)]
    public ?int $expires_in = null;

    /**
     * When the access token expires.
     */
    #[ApiProperty(writable: false, example: '2026-10-21T10:15:00+05:30')]
    public ?string $access_token_expires_at = null;

    /**
     * The opaque refresh token, sent only to the refresh endpoint.
     */
    #[ApiProperty(writable: false, example: '77|3f9c0d...')]
    public ?string $refresh_token = null;

    /**
     * When the refresh token expires.
     */
    #[ApiProperty(writable: false, example: '2026-11-20T10:00:00+05:30')]
    public ?string $refresh_token_expires_at = null;

    /**
     * The session: id, name, client type and absolute expiry.
     */
    #[ApiProperty(writable: false, example: ['id' => 12, 'name' => 'iPhone', 'clientType' => 'mobile', 'expiresAt' => '2027-01-19T10:00:00+05:30'])]
    public ?array $session = null;

    /**
     * The signed-in admin: id, name, email and role.
     */
    #[ApiProperty(writable: false, example: ['id' => 3, 'name' => 'Example Admin', 'email' => 'admin@example.com', 'role' => ['id' => 1, 'name' => 'Administrator', 'permissionType' => 'all']])]
    public ?array $admin = null;

    /**
     * The ACL keys the admin's role grants, or "*" for every permission.
     */
    #[ApiProperty(writable: false, example: ['*'])]
    public ?array $permissions = null;
}

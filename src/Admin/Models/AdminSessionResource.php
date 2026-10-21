<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;

abstract class AdminSessionResource
{
    use AcceptsCamelCaseWrites;

    /**
     * The session id.
     */
    #[ApiProperty(identifier: true, writable: false, example: 12)]
    public ?int $id = null;

    /**
     * The id of the admin the session belongs to.
     */
    #[ApiProperty(writable: false, example: 3)]
    public ?int $admin_id = null;

    /**
     * The name of the admin the session belongs to.
     */
    #[ApiProperty(writable: false, example: 'Example Admin')]
    public ?string $admin_name = null;

    /**
     * The device name the client sent at login.
     */
    #[ApiProperty(writable: false, example: 'iPhone')]
    public ?string $name = null;

    /**
     * The client type: web, mobile, pos, desktop or other.
     */
    #[ApiProperty(writable: false, example: 'mobile')]
    public ?string $client_type = null;

    /**
     * The client address at login.
     */
    #[ApiProperty(writable: false, example: '203.0.113.10')]
    public ?string $ip_address = null;

    /**
     * The user agent at login.
     */
    #[ApiProperty(writable: false, example: 'ExampleApp/1.0')]
    public ?string $user_agent = null;

    /**
     * The client address of the latest request.
     */
    #[ApiProperty(writable: false, example: '203.0.113.11')]
    public ?string $last_ip_address = null;

    /**
     * The user agent of the latest request.
     */
    #[ApiProperty(writable: false, example: 'ExampleApp/1.0')]
    public ?string $last_user_agent = null;

    /**
     * When the session was last used, recorded at most once a minute.
     */
    #[ApiProperty(writable: false, example: '2026-10-21T10:05:00+05:30')]
    public ?string $last_used_at = null;

    /**
     * The channel of the latest request: rest or graphql.
     */
    #[ApiProperty(writable: false, example: 'rest')]
    public ?string $last_channel = null;

    /**
     * When the current refresh token expires.
     */
    #[ApiProperty(writable: false, example: '2026-11-20T10:00:00+05:30')]
    public ?string $refresh_expires_at = null;

    /**
     * When the session reaches its absolute lifetime.
     */
    #[ApiProperty(writable: false, example: '2027-01-19T10:00:00+05:30')]
    public ?string $expires_at = null;

    /**
     * When the session was revoked, or null while it is live.
     */
    #[ApiProperty(writable: false, example: null)]
    public ?string $revoked_at = null;

    /**
     * Why the session was revoked.
     */
    #[ApiProperty(writable: false, example: null)]
    public ?string $revoked_reason = null;

    /**
     * The session status: active, expired or revoked.
     */
    #[ApiProperty(writable: false, example: 'active')]
    public ?string $status = null;

    /**
     * Whether the request was made with this session's access token.
     */
    #[ApiProperty(writable: false, example: true)]
    public ?bool $is_current = null;

    /**
     * When the admin logged in.
     */
    #[ApiProperty(writable: false, example: '2026-10-21T10:00:00+05:30')]
    public ?string $created_at = null;

    /**
     * The confirmation message of a revoke.
     */
    #[ApiProperty(writable: false, example: null)]
    public ?string $message = null;
}

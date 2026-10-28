<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Contracts\SnakeCaseFieldsResource;

abstract class CustomerAuthTokenResponse implements SnakeCaseFieldsResource
{
    use AcceptsCamelCaseWrites;

    /**
     * The token type to send the access token with.
     */
    #[ApiProperty(writable: false, description: 'Always `Bearer`.')]
    public ?string $token_type = null;

    /**
     * The JWT access token.
     */
    #[ApiProperty(writable: false, description: 'Send as `Authorization: Bearer <accessToken>` on every customer request.')]
    public ?string $access_token = null;

    /**
     * Seconds until the access token expires.
     */
    #[ApiProperty(writable: false)]
    public ?int $expires_in = null;

    /**
     * When the access token expires.
     */
    #[ApiProperty(writable: false)]
    public ?string $access_token_expires_at = null;

    /**
     * The opaque refresh token.
     */
    #[ApiProperty(writable: false, description: 'Exchange at `/api/shop/customer/refresh` for a new pair; it works once.')]
    public ?string $refresh_token = null;

    /**
     * When the refresh token expires.
     */
    #[ApiProperty(writable: false)]
    public ?string $refresh_token_expires_at = null;

    /**
     * The session the tokens belong to.
     */
    #[ApiProperty(writable: false, description: 'The session: id, name, clientType, expiresAt.')]
    public ?array $session = null;

    /**
     * The customer the session belongs to.
     */
    #[ApiProperty(writable: false, description: 'The customer: id, firstName, lastName, email.')]
    public ?array $customer = null;
}

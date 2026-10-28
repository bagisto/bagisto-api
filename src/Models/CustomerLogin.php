<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Dto\LoginInput;
use Webkul\BagistoApi\OpenApi\CustomerAuthResponses;
use Webkul\BagistoApi\State\LoginProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerLogin',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/customer/login',
            input: LoginInput::class,
            output: self::class,
            processor: LoginProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Customer Authentication'],
                summary: 'Customer login',
                description: 'Signs a customer in on the current channel with email and password, as the storefront does, and starts a session. Returns a short-lived JWT access token and a refresh token. Optional `device_name` and `client_type` (web, mobile, pos, desktop, other) label the session in the session list. An optional `guest_cart_token` merges that guest cart into the customer\'s cart in the same step and returns the cart in `cartId`; a token that no longer names a guest cart is ignored. An inactive or unverified customer is refused.',
                requestBody: new RequestBody(
                    description: 'Customer login credentials',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['email', 'password'],

                                'properties' => [
                                    'email' => [
                                        'type' => 'string',
                                        'format' => 'email',
                                        'example' => 'john@example.com',
                                    ],

                                    'password' => [
                                        'type' => 'string',
                                        'format' => 'password',
                                        'example' => 'Password123!',
                                    ],

                                    'device_name' => [
                                        'type' => 'string',
                                        'example' => 'iPhone 15',
                                    ],

                                    'client_type' => [
                                        'type' => 'string',
                                        'enum' => CustomerApiSession::CLIENT_TYPES,
                                        'example' => 'mobile',
                                    ],

                                    'guest_cart_token' => [
                                        'type' => 'string',
                                        'description' => 'The token of a guest cart to merge into the customer\'s cart.',
                                        'example' => '2f1c6f7e-5a0b-4c1e-9d0a-1b2c3d4e5f60',
                                    ],
                                ],
                            ],

                            'example' => [
                                'email' => 'john@example.com',
                                'password' => 'Password123!',
                                'device_name' => 'iPhone 15',
                                'client_type' => 'mobile',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Signed in. Send `accessToken` as `Authorization: Bearer <accessToken>`; exchange `refreshToken` at `/api/shop/customer/refresh` before or after the access token expires. `token` is the same access token, kept for one release for clients of the old flow.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => self::LOGIN_EXAMPLE,
                            ],
                        ]),
                    ),
                    '401' => new Response(
                        description: 'Wrong email or password, the same answer whether or not the email exists on this channel (`invalid_credentials`).',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => CustomerAuthResponses::INVALID_CREDENTIALS,
                            ],
                        ]),
                    ),
                    '403' => new Response(
                        description: 'The account is not activated (`account_inactive`) or its email address is not verified yet (`account_unverified`).',
                        content: new \ArrayObject([
                            'application/json' => [
                                'examples' => [
                                    'account_inactive' => ['value' => CustomerAuthResponses::ACCOUNT_INACTIVE],
                                    'account_unverified' => ['value' => CustomerAuthResponses::ACCOUNT_UNVERIFIED],
                                ],
                            ],
                        ]),
                    ),
                    '429' => new Response(
                        description: 'Too many failed logins for this email and address, five in 60 seconds by default (`too_many_login_attempts`). Wait for `Retry-After` seconds.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => CustomerAuthResponses::TOO_MANY_LOGIN_ATTEMPTS,
                            ],
                        ]),
                        headers: new \ArrayObject(CustomerAuthResponses::RETRY_AFTER),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: LoginInput::class,
            output: self::class,
            processor: LoginProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            description: 'Sign a customer in on the current channel and return an access and refresh token pair. An optional guestCartToken merges that guest cart into the customer cart and returns its id in cartId. Errors carry extensions.code: invalid_credentials, account_inactive, account_unverified or too_many_login_attempts.',
        ),
    ]
)]
class CustomerLogin extends CustomerAuthTokenResponse
{
    /**
     * An example of the login answer.
     */
    public const LOGIN_EXAMPLE = CustomerAuthResponses::TOKEN_PAIR + [
        'id' => 1529,
        '_id' => 1529,
        'apiToken' => 'PPKI0VNiX5qehio2q1cMeMd6TCaIKofwQ4mb7y2zcy0japYwsyc',
        'token' => 'eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6Ii4uLiJ9.eyJ0eXAiOiJjdXN0b21lcl9hY2Nlc3MiLCJzdWIiOjE1Mjl9.c2lnbmF0dXJl',
        'cartId' => null,
        'success' => true,
        'message' => 'You have logged in successfully',
    ];

    /**
     * The customer id.
     */
    #[ApiProperty(identifier: false, writable: false, readable: true, required: false)]
    public ?int $id = null;

    /**
     * The customer id.
     */
    #[ApiProperty(writable: false, readable: true, required: false)]
    public ?int $_id = null;

    /**
     * The customer's device push token, not an authentication token.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $api_token = null;

    /**
     * The access token again, kept for one release for clients of the old flow.
     */
    #[ApiProperty(writable: false, readable: true, description: 'Deprecated: the same value as `accessToken`.')]
    public ?string $token = null;

    /**
     * The customer's cart the guest cart was merged into, or null when no guest cart was merged.
     */
    #[ApiProperty(writable: false, readable: true, description: 'The id of the customer cart a `guestCartToken` was merged into; null when no guest cart was merged.')]
    public ?int $cart_id = null;

    /**
     * The message.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;

    /**
     * Whether the login succeeded.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;
}

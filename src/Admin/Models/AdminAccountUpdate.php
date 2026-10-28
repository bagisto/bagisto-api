<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminAccountUpdateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminAccountUpdateProcessor;
use Webkul\BagistoApi\OpenApi\AdminAuthResponses;
use Webkul\BagistoApi\OpenApi\ApiSchemas;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAccountUpdate',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Put(
            uriTemplate: '/account',
            input: AdminAccountUpdateInput::class,
            output: self::class,
            processor: AdminAccountUpdateProcessor::class,
            extraProperties: ['standard_put' => false],
            read: false,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Authentication'],
                summary: 'Update my account',
                description: "Updates the signed-in admin's name, email and password, as the admin panel's *My Account* screen does; `currentPassword` is always required. Changing the password revokes the admin's other sessions (the current one stays signed in) when *Revoke Sessions on Password Change* is on, and every API token when *Revoke API Tokens on Password Change* is on. Needs a login session access token, not an API token.",
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'current_password'],

                                'properties' => [
                                    'name' => [
                                        'type' => 'string',
                                        'example' => 'Example Admin',
                                    ],

                                    'email' => [
                                        'type' => 'string',
                                        'format' => 'email',
                                        'example' => 'admin@example.com',
                                    ],

                                    'password' => [
                                        'type' => 'string',
                                        'format' => 'password',
                                        'minLength' => 6,
                                        'example' => 'new-secret',
                                    ],

                                    'password_confirmation' => [
                                        'type' => 'string',
                                        'format' => 'password',
                                        'example' => 'new-secret',
                                    ],

                                    'current_password' => [
                                        'type' => 'string',
                                        'format' => 'password',
                                        'example' => 'admin123',
                                    ],
                                ],
                            ],

                            'example' => [
                                'name' => 'Example Admin',
                                'email' => 'admin@example.com',
                                'password' => 'new-secret',
                                'password_confirmation' => 'new-secret',
                                'current_password' => 'admin123',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'Account updated.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'name' => 'Example Admin',
                                    'email' => 'admin@example.com',
                                    'image' => null,
                                    'roleId' => 1,
                                    'roleName' => 'Administrator',
                                    'passwordChanged' => true,
                                    'revokedSessions' => 2,
                                    'revokedApiTokens' => 0,
                                    'success' => true,
                                    'message' => 'Account updated successfully',
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'The access token is missing or invalid, or its session was revoked (`unauthenticated`), or it expired (`access_token_expired`).'),
                    '403' => new Model\Response(description: 'Called with an API token (`access_token_required`).'),
                    '422' => new Model\Response(
                        description: 'Validation failure (a problem document; `detail` holds the first message), such as a missing `name` or `current_password`, an email another admin uses or a password confirmation that does not match, or a wrong current password (`invalid_current_password`).',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'oneOf' => [
                                        ['$ref' => ApiSchemas::PROBLEM_REF],
                                        ['$ref' => ApiSchemas::AUTH_ERROR_REF],
                                    ],
                                ],
                                'examples' => [
                                    'validation_failed' => [
                                        'value' => [
                                            'type' => '/errors/422',
                                            'title' => 'Bad Request',
                                            'status' => 422,
                                            'detail' => 'The current password field is required.',
                                        ],
                                    ],
                                    'invalid_current_password' => ['value' => AdminAuthResponses::INVALID_CURRENT_PASSWORD],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminAccountUpdateInput::class,
            output: self::class,
            processor: AdminAccountUpdateProcessor::class,
            description: "Update the signed-in admin's name, email and password; currentPassword is required. Becomes createAdminAccountUpdate; needs a login session access token.",
        ),
    ],
)]
class AdminAccountUpdate
{
    use AcceptsCamelCaseWrites;

    /**
     * The admin id.
     */
    #[ApiProperty(writable: false, example: 1)]
    public ?int $id = null;

    /**
     * The name.
     */
    #[ApiProperty(writable: false, example: 'Example Admin')]
    public ?string $name = null;

    /**
     * The email address.
     */
    #[ApiProperty(writable: false, example: 'admin@example.com')]
    public ?string $email = null;

    /**
     * The stored image path.
     */
    #[ApiProperty(writable: false, example: null)]
    public ?string $image = null;

    /**
     * The role id.
     */
    #[ApiProperty(writable: false, example: 1)]
    public ?int $role_id = null;

    /**
     * The role name.
     */
    #[ApiProperty(writable: false, example: 'Administrator')]
    public ?string $role_name = null;

    /**
     * Whether the password was changed.
     */
    #[ApiProperty(writable: false, example: false)]
    public ?bool $password_changed = null;

    /**
     * How many other sessions the password change revoked.
     */
    #[ApiProperty(writable: false, example: 0)]
    public ?int $revoked_sessions = null;

    /**
     * How many API tokens the password change revoked.
     */
    #[ApiProperty(writable: false, example: 0)]
    public ?int $revoked_api_tokens = null;

    /**
     * Whether the update succeeded.
     */
    #[ApiProperty(writable: false, example: true)]
    public ?bool $success = null;

    /**
     * The confirmation message.
     */
    #[ApiProperty(writable: false, example: 'Account updated successfully')]
    public ?string $message = null;
}

<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminResetPasswordInput;
use Webkul\BagistoApi\Admin\State\AdminPasswordProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminResetPassword',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/auth/reset-password',
            input: AdminResetPasswordInput::class,
            output: self::class,
            processor: AdminPasswordProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Authentication'],
                summary: 'Reset a password with the emailed token',
                description: "Sets a new password with the token from the reset email, through core's admin password broker. Every API session of the admin is revoked, and every API token too when *Revoke API Tokens on Password Change* is on; log in again afterwards. Five requests a minute per client address are allowed.",
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['token', 'email', 'password', 'password_confirmation'],

                                'properties' => [
                                    'token' => [
                                        'type' => 'string',
                                        'example' => '6f1c0e...',
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
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'Password reset; every session was revoked.'),
                    '422' => new Model\Response(description: 'Validation failure, or the token or email does not match a reset request (`password_reset_token_invalid`).'),
                    '429' => new Model\Response(description: 'Too many requests (`too_many_password_requests`); see `Retry-After`.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminResetPasswordInput::class,
            output: self::class,
            processor: AdminPasswordProcessor::class,
            description: 'Reset an admin password with the emailed token; revokes every API session. Becomes createAdminResetPassword; reachable without a token.',
        ),
    ],
)]
class AdminResetPassword
{
    /**
     * Whether the password was reset.
     */
    #[ApiProperty(writable: false, example: true)]
    public ?bool $success = null;

    /**
     * The confirmation message.
     */
    #[ApiProperty(writable: false, example: 'Your password has been reset. Please log in again.')]
    public ?string $message = null;
}

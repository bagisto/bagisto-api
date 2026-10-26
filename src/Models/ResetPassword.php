<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Dto\ResetPasswordInput;
use Webkul\BagistoApi\State\ResetPasswordProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ResetPassword',
    operations: [
        new Post(
            uriTemplate: '/reset-passwords',
            processor: ResetPasswordProcessor::class,
            normalizationContext: ['skip_null_values' => false],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Customer Authentication'],
                summary: 'Reset customer password using token',
                description: 'Resets the password for a customer using the token received via email.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['token', 'email', 'password', 'password_confirmation'],

                                'properties' => [
                                    'token' => [
                                        'type' => 'string',
                                        'example' => 'a1b2c3d4e5...',
                                    ],

                                    'email' => [
                                        'type' => 'string',
                                        'example' => 'john@example.com',
                                    ],

                                    'password' => [
                                        'type' => 'string',
                                        'example' => 'NewPassword123!',
                                    ],

                                    'password_confirmation' => [
                                        'type' => 'string',
                                        'example' => 'NewPassword123!',
                                    ],
                                ],
                            ],

                            'example' => [
                                'token' => 'a1b2c3d4e5...',
                                'email' => 'john@example.com',
                                'password' => 'NewPassword123!',
                                'password_confirmation' => 'NewPassword123!',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Password reset result',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Your password has been reset.',
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
            input: ResetPasswordInput::class,
            output: self::class,
            processor: ResetPasswordProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
        ),
    ]
)]
class ResetPassword
{
    /**
     * The success.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    /**
     * The message.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;
}

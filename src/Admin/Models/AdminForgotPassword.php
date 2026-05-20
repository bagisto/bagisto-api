<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Webkul\BagistoApi\Admin\Dto\AdminForgotPasswordInput;
use Webkul\BagistoApi\Admin\State\AdminForgotPasswordProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminForgotPassword',
    operations: [
        new Post(
            uriTemplate: '/forgot-password',
            input: AdminForgotPasswordInput::class,
            output: self::class,
            processor: AdminForgotPasswordProcessor::class,
            normalizationContext: ['skip_null_values' => false],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Admin Authentication'],
                summary: 'Admin forgot password',
                description: 'Sends a password reset email to the given admin email if the account exists.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['email'],
                                'properties' => [
                                    'email' => ['type' => 'string', 'example' => 'admin@example.com'],
                                ],
                            ],
                            'example' => [
                                'email' => 'admin@example.com',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Reset-link request processed.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'A password reset link has been sent to your email.',
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
            input: AdminForgotPasswordInput::class,
            output: self::class,
            processor: AdminForgotPasswordProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
        ),
    ]
)]
class AdminForgotPassword
{
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;
}

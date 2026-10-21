<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminForgotPasswordInput;
use Webkul\BagistoApi\Admin\State\AdminPasswordProcessor;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminForgotPassword',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Post(
            uriTemplate: '/auth/forgot-password',
            input: AdminForgotPasswordInput::class,
            output: self::class,
            processor: AdminPasswordProcessor::class,
            status: 200,
            openapi: new Model\Operation(
                tags: ['Admin Authentication'],
                summary: 'Email a password reset link',
                description: "Sends core's admin password reset email when the address belongs to an admin. The answer is the same whether or not it does. Five requests a minute per client address are allowed.",
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['email'],

                                'properties' => [
                                    'email' => [
                                        'type' => 'string',
                                        'format' => 'email',
                                        'example' => 'admin@example.com',
                                    ],
                                ],
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(description: 'The generic confirmation, whether or not the email exists.'),
                    '422' => new Model\Response(description: 'Validation failure.'),
                    '429' => new Model\Response(description: 'Too many requests (`too_many_password_requests`); see `Retry-After`.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: AdminForgotPasswordInput::class,
            output: self::class,
            processor: AdminPasswordProcessor::class,
            description: 'Email an admin password reset link; the answer never says whether the email exists. Becomes createAdminForgotPassword; reachable without a token.',
        ),
    ],
)]
class AdminForgotPassword
{
    /**
     * Whether the request was accepted.
     */
    #[ApiProperty(writable: false, example: true)]
    public ?bool $success = null;

    /**
     * The confirmation message.
     */
    #[ApiProperty(writable: false, example: 'Reset Password link sent')]
    public ?string $message = null;
}

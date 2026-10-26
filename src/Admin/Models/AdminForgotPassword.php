<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminForgotPasswordInput;
use Webkul\BagistoApi\Admin\State\AdminPasswordProcessor;
use Webkul\BagistoApi\OpenApi\AdminAuthResponses;
use Webkul\BagistoApi\OpenApi\ApiSchemas;

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
                description: "Sends core's admin password reset email when the address belongs to an admin. The answer is the same whether or not it does. Five requests a minute per client address are allowed by default (configurable).",
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

                            'example' => [
                                'email' => 'admin@example.com',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '200' => new Model\Response(
                        description: 'The generic confirmation, whether or not the email belongs to an admin.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',

                                    'properties' => [
                                        'success' => ['type' => 'boolean'],
                                        'message' => ['type' => 'string'],
                                    ],
                                ],
                                'example' => [
                                    'success' => true,
                                    'message' => 'Reset Password link sent',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(
                        description: 'Validation failure: a missing or malformed `email`. `detail` holds the first message.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::PROBLEM_REF],
                                'example' => AdminAuthResponses::VALIDATION_FAILED,
                            ],
                        ]),
                    ),
                    '429' => new Model\Response(
                        description: 'More than the forgot-password requests allowed from this address in a minute, five by default (`too_many_password_requests`), or more than the requests allowed a minute, 60 by default (`rate_limit_exceeded`). Wait for `Retry-After` seconds.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => ['$ref' => ApiSchemas::AUTH_ERROR_REF],
                                'examples' => [
                                    'too_many_password_requests' => ['value' => AdminAuthResponses::TOO_MANY_PASSWORD_REQUESTS],
                                    'rate_limit_exceeded' => ['value' => AdminAuthResponses::RATE_LIMIT_EXCEEDED],
                                ],
                            ],
                        ]),
                        headers: new \ArrayObject(AdminAuthResponses::RETRY_AFTER),
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

<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Dto\ForgotPasswordInput;
use Webkul\BagistoApi\State\ForgotPasswordProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ForgotPassword',
    operations: [
        new Post(
            uriTemplate: '/forgot-passwords',
            processor: ForgotPasswordProcessor::class,
            normalizationContext: ['skip_null_values' => false],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Customer Authentication'],
                summary: 'Request password reset link',
                description: 'Sends a password reset email when the address belongs to a customer of the current channel. The answer is the same whether or not it does, so the endpoint cannot be used to discover registered addresses.',
                requestBody: new RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['email'],

                                'properties' => [
                                    'email' => [
                                        'type' => 'string',
                                        'example' => 'john@example.com',
                                    ],
                                ],
                            ],

                            'example' => [
                                'email' => 'john@example.com',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Reset link sent',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Reset link sent successfully to your email',
                                ],
                            ],
                        ]),
                    ),
                    '422' => new Response(
                        description: 'The email is missing',
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: ForgotPasswordInput::class,
            output: self::class,
            processor: ForgotPasswordProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
        ),
    ]
)]
class ForgotPassword
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

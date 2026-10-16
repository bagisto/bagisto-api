<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Dto\ResendVerificationEmailInput;
use Webkul\BagistoApi\State\ResendVerificationEmailProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'ResendVerificationEmail',
    operations: [
        new Post(
            uriTemplate: '/verification-emails',
            processor: ResendVerificationEmailProcessor::class,
            normalizationContext: ['skip_null_values' => false],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Customer'],
                summary: 'Resend customer verification email',
                description: 'Issues a fresh email verification token and re-sends the verification email for the given address. The response is the same whether or not the address exists, so it cannot be used to discover registered emails.',
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
                        description: 'Verification email result',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'success' => true,
                                    'message' => 'Verification email sent',
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
            input: ResendVerificationEmailInput::class,
            output: self::class,
            processor: ResendVerificationEmailProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
            ],
        ),
    ]
)]
class ResendVerificationEmail
{
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;
}

<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
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
                'groups'                 => ['mutation'],
            ],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer'],
                summary: 'Request password reset link',
                description: 'Sends a password reset email to the given customer email if the account exists.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type'       => 'object',
                                'required'   => ['email'],
                                'properties' => [
                                    'email' => ['type' => 'string', 'example' => 'customer@example.com'],
                                ],
                            ],
                        ],
                    ]),
                ),
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
                'groups'                 => ['mutation'],
            ],
        ),
    ]
)]
class ForgotPassword
{
    #[ApiProperty(writable: false, readable: true)]
    public ?bool $success = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;
}

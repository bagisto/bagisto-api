<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Webkul\BagistoApi\Dto\VerifyTokenInput;
use Webkul\BagistoApi\State\VerifyTokenProcessor;

#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'VerifyToken',
    operations: [
        new Post(
            uriTemplate: '/verify-tokens',
            processor: VerifyTokenProcessor::class,
            normalizationContext: ['skip_null_values' => false],
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer'],
                summary: 'Verify customer bearer token',
                description: 'Validates the customer bearer token from the Authorization header and returns the customer details if valid.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => ['type' => 'object', 'properties' => new \ArrayObject],
                            'example' => new \ArrayObject,
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Mutation(
            name: 'create',
            input: VerifyTokenInput::class,
            output: self::class,
            processor: VerifyTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
        ),
    ]
)]
class VerifyToken
{
    #[ApiProperty(identifier: false, writable: false, readable: true, required: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $firstName = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $lastName = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $email = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?bool $isValid = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $message = null;
}

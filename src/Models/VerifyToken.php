<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
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
                'groups' => ['mutation'],
            ],
            openapi: new Operation(
                tags: ['Customer'],
                summary: 'Verify customer bearer token',
                description: 'Validates the customer bearer token from the Authorization header and returns the customer details if valid.',
                requestBody: new RequestBody(
                    required: false,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object', 'properties' => new \ArrayObject],
                            'example' => new \ArrayObject,
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
                        description: 'Token is valid',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1529,
                                    'firstName' => 'Api',
                                    'lastName' => 'Doc',
                                    'email' => 'john@example.com',
                                    'isValid' => true,
                                    'message' => 'Token is valid',
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
            input: VerifyTokenInput::class,
            output: self::class,
            processor: VerifyTokenProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups' => ['mutation'],
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

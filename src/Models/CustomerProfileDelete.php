<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use Webkul\BagistoApi\Dto\CustomerProfileInput;
use Webkul\BagistoApi\State\CustomerProfileProcessor;

/**
 * Customer profile delete resource
 * Handles authenticated customer profile deletion
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'CustomerProfileDelete',
    uriTemplate: '/customer-profile-deletes',
    operations: [
        new Post(
            uriTemplate: '/customer-profile-deletes/{id}',
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Customer'],
                summary: 'Delete customer profile',
                description: 'Delete the authenticated customer\'s account. Requires Bearer token.',
                requestBody: new \ApiPlatform\OpenApi\Model\RequestBody(
                    description: 'Confirm deletion with the account password.',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => [
                                'type'       => 'object',
                                'required'   => ['password'],
                                'properties' => [
                                    'password' => ['type' => 'string', 'format' => 'password', 'example' => 'Password123!'],
                                ],
                            ],
                            'example' => [
                                'password' => 'Password123!',
                            ],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new \ApiPlatform\OpenApi\Model\Response(
                        description: 'Customer account deleted',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [],
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
            input: CustomerProfileInput::class,
            output: false,
            processor: CustomerProfileProcessor::class,
            denormalizationContext: [
                'allow_extra_attributes' => true,
                'groups'                 => ['mutation'],
            ],
            description: 'Delete authenticated customer profile (requires token)',
        ),
    ]
)]
class CustomerProfileDelete
{
    #[ApiProperty(readable: true, writable: false)]
    public ?string $id = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $token = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $firstName = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $lastName = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $email = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $phone = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $gender = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $dateOfBirth = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $password = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $confirmPassword = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $status = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?bool $subscribedToNewsLetter = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $isVerified = null;

    #[ApiProperty(readable: true, writable: false)]
    public ?string $isSuspended = null;
}

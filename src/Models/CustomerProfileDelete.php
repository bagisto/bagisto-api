<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
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
            processor: CustomerProfileProcessor::class,
            openapi: new Operation(
                tags: ['Customer'],
                summary: 'Delete customer profile',
                description: 'Delete the authenticated customer\'s account. Requires the Bearer token and the account password, as the storefront does. An account with a pending or processing order cannot be deleted (422).',
                requestBody: new RequestBody(
                    description: 'The account password. The account itself is identified by the Bearer token.',
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['password'],
                                'properties' => ['password' => ['type' => 'string']],
                            ],
                            'example' => ['password' => 'Secret@123'],
                        ],
                    ]),
                ),
                responses: [
                    '201' => new Response(
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
                'groups' => ['mutation'],
            ],
            description: 'Delete the authenticated customer profile. Requires the token and `password`.',
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

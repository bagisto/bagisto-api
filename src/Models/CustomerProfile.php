<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Resolver\CustomerQueryResolver;
use Webkul\BagistoApi\State\CustomerProfileCollectionProvider;

/**
 * Authenticated customer profile read operation
 */
#[ApiResource(
    routePrefix: '/api/shop',
    uriTemplate: '/customer-profiles',
    shortName: 'CustomerProfile',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/customer-profile',
            provider: CustomerProfileCollectionProvider::class,
            paginationEnabled: false,
            normalizationContext: [
                'skip_null_values' => false,
            ],
            openapi: new Operation(
                tags: ['Customer'],
                summary: 'Get authenticated customer profile',
                description: 'Returns the profile of the currently authenticated customer. Requires Bearer token via the Authorize button.',
                responses: [
                    '200' => new Response(
                        description: 'Authenticated customer profile',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => '1529',
                                        'firstName' => 'Api',
                                        'lastName' => 'Doc',
                                        'email' => 'john@example.com',
                                        'phone' => null,
                                        'gender' => null,
                                        'dateOfBirth' => null,
                                        'status' => '1',
                                        'subscribedToNewsLetter' => false,
                                        'isVerified' => '0',
                                        'isSuspended' => '0',
                                        'image' => null,
                                        'password' => null,
                                        'confirmPassword' => null,
                                        'success' => null,
                                        'message' => null,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'read',
            resolver: CustomerQueryResolver::class,
            args: [],
            normalizationContext: [
                'groups' => ['query'],
            ],
            description: 'Read authenticated customer profile using Bearer token in Authorization header. Returns the authenticated customer\'s profile data.',
        ),
    ]
)]
class CustomerProfile
{
    #[ApiProperty(readable: true, writable: false, identifier: true)]
    #[Groups(['query'])]
    public ?string $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $first_name = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $last_name = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $email = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $phone = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $gender = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $date_of_birth = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $status = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?bool $subscribed_to_news_letter = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $is_verified = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $is_suspended = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $image = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $password = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['mutation'])]
    public ?string $confirmPassword = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?bool $success = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query', 'mutation'])]
    public ?string $message = null;
}

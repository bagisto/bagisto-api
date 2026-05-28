<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminProfileQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminProfileProvider;

/**
 * Read the authenticated admin's own profile.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminProfile',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/get',
            provider: AdminProfileProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new \ApiPlatform\OpenApi\Model\Operation(
                tags: ['Admin Authentication'],
                summary: "Get logged-in admin user's details",
                description: 'Returns the profile of the currently authenticated admin. Requires a Bearer token.',
                responses: [
                    '200' => new \ApiPlatform\OpenApi\Model\Response(
                        description: "The authenticated admin's profile.",
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id'       => '1',
                                        'name'     => 'Example Admin',
                                        'email'    => 'admin@example.com',
                                        'image'    => null,
                                        'status'   => '1',
                                        'roleId'   => 1,
                                        'roleName' => 'Administrator',
                                        'success'  => true,
                                        'message'  => null,
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
            resolver: AdminProfileQueryResolver::class,
            args: [],
            normalizationContext: ['groups' => ['query']],
            description: "Read the authenticated admin's profile using the Bearer token in the Authorization header.",
        ),
    ]
)]
class AdminProfile
{
    #[ApiProperty(readable: true, writable: false, identifier: true)]
    #[Groups(['query'])]
    public ?string $id = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $name = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $email = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $image = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $status = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?int $roleId = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $roleName = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?bool $success = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $message = null;
}

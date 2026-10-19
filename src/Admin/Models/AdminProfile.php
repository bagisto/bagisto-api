<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\Resolver\AdminProfileQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminProfileProvider;

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
            openapi: new Operation(
                tags: ['Admin Authentication'],
                summary: "Get logged-in admin user's details",
                description: 'Returns the profile of the currently authenticated admin. Requires a Bearer token.',
                responses: [
                    '200' => new Response(
                        description: "The authenticated admin's profile.",
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => '1',
                                        'name' => 'Example Admin',
                                        'email' => 'admin@example.com',
                                        'image' => null,
                                        'status' => '1',
                                        'roleId' => 1,
                                        'roleName' => 'Administrator',
                                        'success' => true,
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
            resolver: AdminProfileQueryResolver::class,
            args: [],
            normalizationContext: ['groups' => ['query']],
            description: "Read the authenticated admin's profile using the Bearer token in the Authorization header.",
        ),
    ]
)]
class AdminProfile
{
    use AcceptsCamelCaseWrites;

    /**
     * The id.
     */
    #[ApiProperty(readable: true, writable: false, identifier: true)]
    #[Groups(['query'])]
    public ?string $id = null;

    /**
     * The name.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $name = null;

    /**
     * The email.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $email = null;

    /**
     * The image.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $image = null;

    /**
     * The status.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $status = null;

    /**
     * The role id.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?int $role_id = null;

    /**
     * The role name.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $role_name = null;

    /**
     * The success.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?bool $success = null;

    /**
     * The message.
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $message = null;
}

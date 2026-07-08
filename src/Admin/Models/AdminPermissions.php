<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminPermissionsQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminPermissionsProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminPermissions',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/permissions',
            provider: AdminPermissionsProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Menu'],
                summary: 'Authenticated admin permissions',
                description: 'Returns the effective permission set for the authenticated token: `permissionType` (`all` / `custom` / `same_as_web`) and the granted `permissions` keys (capped by the admin\'s role). `permissionType: "all"` returns `permissions: ["*"]` (full access). Use this to gate UI actions without trial-and-error 403s.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The token\'s effective permissions.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [[
                                    'id' => 'permissions',
                                    'permissionType' => 'custom',
                                    'permissions' => ['catalog', 'catalog.products', 'sales.orders'],
                                ]],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'get',
            resolver: AdminPermissionsQueryResolver::class,
            args: [],
            normalizationContext: ['skip_null_values' => false],
        ),
    ],
)]
class AdminPermissions
{
    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['query'])]
    public ?string $id = 'permissions';

    #[ApiProperty(writable: false)]
    #[Groups(['query'])]
    public ?string $permission_type = null;

    /**
     * @var array<int, string>|null
     */
    #[ApiProperty(writable: false)]
    #[Groups(['query'])]
    public ?array $permissions = null;
}

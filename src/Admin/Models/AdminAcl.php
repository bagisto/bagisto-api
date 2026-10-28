<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminAclQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminAclProvider;

/**
 * The full ACL permission tree the admin role screen renders, so a client can
 * discover every key it may grant to a custom role.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminAcl',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/acl',
            provider: AdminAclProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Settings: Roles'],
                summary: 'ACL permission tree',
                description: 'Returns every permission the admin role screen offers, as a nested tree sorted the same way. Each node carries its `key` (the value to send in a role\'s `permissions`), its translated `name`, the admin `route` it guards, its `sort` and its `children`.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The ACL tree.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [[
                                    'id' => 'acl',
                                    'tree' => [
                                        [
                                            'key' => 'dashboard',
                                            'name' => 'Dashboard',
                                            'route' => 'admin.dashboard.index',
                                            'sort' => 1,
                                            'children' => [],
                                        ],
                                        [
                                            'key' => 'sales',
                                            'name' => 'Sales',
                                            'route' => 'admin.sales.orders.index',
                                            'sort' => 2,
                                            'children' => [[
                                                'key' => 'sales.orders',
                                                'name' => 'Orders',
                                                'route' => 'admin.sales.orders.index',
                                                'sort' => 1,
                                                'children' => [],
                                            ]],
                                        ],
                                    ],
                                ]],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Unauthenticated.'),
                    '403' => new Model\Response(description: 'Missing the settings.roles permission.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'get',
            resolver: AdminAclQueryResolver::class,
            args: [],
            normalizationContext: ['groups' => ['query']],
            description: 'The ACL permission tree the admin role screen renders.',
        ),
    ],
)]
class AdminAcl
{
    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['query'])]
    public ?string $id = 'acl';

    /**
     * @var array<int, array<string, mixed>>|null
     */
    #[ApiProperty(writable: false, description: 'Nested ACL nodes: key, name, route, sort, children.')]
    #[Groups(['query'])]
    public ?array $tree = null;
}

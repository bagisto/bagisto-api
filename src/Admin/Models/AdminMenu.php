<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminMenuQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminMenuProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminMenu',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/menu',
            provider: AdminMenuProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Menu'],
                summary: 'Admin navigation menu',
                description: 'Returns the admin sidebar menu as a nested tree, filtered to what the authenticated token\'s admin role is allowed to see. Each node carries its label, ACL permission key, hierarchy, and a mapping to the matching API resource (REST path + GraphQL field), or `apiResource: null` for group headers and panel-only screens that have no API endpoint.',
                responses: [
                    '200' => new Model\Response(
                        description: 'The permission-filtered admin menu tree.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [[
                                    'id' => 'menu',
                                    'tree' => [
                                        [
                                            'key' => 'sales',
                                            'label' => 'Sales',
                                            'icon' => 'icon-sales',
                                            'sort' => 2,
                                            'permission' => 'sales',
                                            'apiResource' => null,
                                            'children' => [[
                                                'key' => 'sales.orders',
                                                'label' => 'Orders',
                                                'icon' => null,
                                                'sort' => 1,
                                                'permission' => 'sales.orders',
                                                'apiResource' => [
                                                    'rest' => '/api/admin/orders',
                                                    'graphql' => 'adminOrders',
                                                ],
                                                'children' => [],
                                            ]],
                                        ],
                                    ],
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
            resolver: AdminMenuQueryResolver::class,
            args: [],
            normalizationContext: ['skip_null_values' => false],
        ),
    ],
)]
class AdminMenu
{
    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['query'])]
    public ?string $id = 'menu';

    /**
     * @var array<int, array<string, mixed>>|null
     */
    #[ApiProperty(writable: false)]
    #[Groups(['query'])]
    public ?array $tree = null;
}

<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminConfigurationSearchQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminConfigurationSearchProvider;

/**
 * Text search over the configuration tree, mirroring the search box on the admin
 * Configuration screen.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminConfiguration',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/configuration/search',
            provider: AdminConfigurationSearchProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Configuration'],
                summary: 'Search configuration',
                description: 'Case-insensitive search over the titles of every configuration section, group and field. Each match carries its breadcrumb `title`, the admin panel `url`, and the `slug` to pass to `GET /configuration?slug=` and `POST /configuration`.',
                parameters: [
                    new Model\Parameter('query', 'query', 'Search term.', true, schema: ['type' => 'string', 'example' => 'reorder']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Matching configuration entries.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [[
                                    'id' => 'configuration-search',
                                    'query' => 'reorder',
                                    'results' => [[
                                        'title' => 'Sales > Order Settings > Allow Reorder',
                                        'url' => 'https://example.com/admin/configuration/sales/order_settings',
                                        'slug' => 'sales.order_settings',
                                    ]],
                                ]],
                            ],
                        ]),
                    ),
                    '422' => new Model\Response(description: 'Missing search query.'),
                    '401' => new Model\Response(description: 'Unauthenticated.'),
                    '403' => new Model\Response(description: 'Missing the configuration permission.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'search',
            resolver: AdminConfigurationSearchQueryResolver::class,
            args: ['query' => ['type' => 'String!']],
            normalizationContext: ['groups' => ['query']],
            description: 'Search the configuration tree. Becomes searchAdminConfiguration.',
        ),
    ],
)]
class AdminConfigurationSearch
{
    #[ApiProperty(identifier: true, writable: false)]
    #[Groups(['query'])]
    public ?string $id = 'configuration-search';

    #[ApiProperty(writable: false)]
    #[Groups(['query'])]
    public ?string $query = null;

    /**
     * @var array<int, array<string, string>>|null
     */
    #[ApiProperty(writable: false, description: 'Matches: title (breadcrumb), url (admin panel) and slug.')]
    #[Groups(['query'])]
    public ?array $results = null;
}

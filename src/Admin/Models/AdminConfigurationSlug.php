<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminConfigurationSlugQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminConfigurationSlugProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminConfigurationSlug',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/configuration/slugs',
            provider: AdminConfigurationSlugProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Configuration'],
                summary: 'Configuration slugs (discovery)',
                description: 'Lists every registered configuration slug (section/group key) with its label, so a client can discover the valid slugs to pass to the configuration values endpoint.',
                responses: [
                    '200' => new Model\Response(
                        description: 'List of configuration slugs.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [[
                                    'id' => 'configuration-slugs',
                                    'slugs' => [
                                        ['slug' => 'general', 'name' => 'General', 'sort' => 1, 'hasFields' => false, 'hasChildren' => true],
                                        ['slug' => 'sales.order_settings', 'name' => 'Order Settings', 'sort' => 2, 'hasFields' => true, 'hasChildren' => false],
                                    ],
                                ]],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Unauthenticated.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'list',
            resolver: AdminConfigurationSlugQueryResolver::class,
            args: [],
            normalizationContext: ['groups' => ['query']],
            description: 'Lists every registered configuration slug for discovery.',
        ),
    ],
)]
class AdminConfigurationSlug
{
    #[ApiProperty(identifier: true, readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $id = 'configuration-slugs';

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?array $slugs = null;
}

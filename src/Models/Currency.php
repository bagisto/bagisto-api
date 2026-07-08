<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    operations: [
        new Get(
            openapi: new Operation(
                tags: ['Currency'],
                summary: 'Get a currency by ID',
                description: 'Returns one store currency. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'The currency.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'code' => 'USD',
                                    'name' => 'US Dollar',
                                    'symbol' => '$',
                                    'decimal' => 2,
                                    'groupSeparator' => ',',
                                    'decimalSeparator' => '.',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'Currency not found.'),
                ],
            ),
        ),
        new GetCollection(
            paginationClientItemsPerPage: true,
            openapi: new Operation(
                tags: ['Currency'],
                summary: 'List currencies',
                description: 'Returns all store currencies. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'List of currencies.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 1,
                                        'code' => 'USD',
                                        'name' => 'US Dollar',
                                        'symbol' => '$',
                                        'decimal' => 2,
                                        'groupSeparator' => ',',
                                        'decimalSeparator' => '.',
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
        new Query(resolver: BaseQueryItemResolver::class),
        new QueryCollection(provider: CursorAwareCollectionProvider::class),
    ]
)]
class Currency extends \Webkul\Core\Models\Currency
{
    /**
     * API Platform identifier
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }
}

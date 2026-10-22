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
        new GetCollection(
            openapi: new Operation(
                tags: ['AttributeTranslation'],
                summary: 'List attribute translations',
                description: 'Returns the localized names of catalog attributes. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'Attribute translation collection.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 1,
                                        'attributeId' => 1,
                                        'locale' => 'en',
                                        'name' => 'SKU',
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            openapi: new Operation(
                tags: ['AttributeTranslation'],
                summary: 'Get an attribute translation',
                description: 'Returns a single attribute translation by its identifier. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'Attribute translation.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'attributeId' => 1,
                                    'locale' => 'en',
                                    'name' => 'SKU',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(
                        description: 'Translation not found.',
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: CursorAwareCollectionProvider::class,
            description: 'Every attribute translation (one row per attribute and locale), ordered by ID. Cursor pagination: `first` takes the first N rows (after `after` when given), `last` without `before` takes the last N, and `before` takes the N rows before that cursor; N defaults to 10 and is capped at 50, and `first: 0` returns an empty page. Needs the X-STOREFRONT-KEY header.',
        ),
        new Query(
            resolver: BaseQueryItemResolver::class,
            description: 'One attribute translation. Pass the IRI or the numeric ID as `id`; an unknown ID is an error. Needs the X-STOREFRONT-KEY header.',
        ),
    ],
)]
class AttributeTranslation extends \Webkul\Attribute\Models\AttributeTranslation
{
    /**
     * API Platform identifier.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }
}

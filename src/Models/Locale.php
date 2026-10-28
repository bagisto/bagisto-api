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
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get(
            openapi: new Operation(
                tags: ['Locale'],
                summary: 'Get a locale by ID',
                description: 'Returns a single store locale. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'The locale.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'id' => 1,
                                    'code' => 'en',
                                    'name' => 'English',
                                    'direction' => 'ltr',
                                    'logoPath' => 'locales/en.png',
                                    'createdAt' => null,
                                    'updatedAt' => null,
                                    'logoUrl' => 'http://localhost:8000/storage/locales/en.png',
                                ],
                            ],
                        ]),
                    ),
                    '404' => new Response(description: 'Locale not found.'),
                ],
            ),
        ),
        new GetCollection(
            paginationClientItemsPerPage: true,
            openapi: new Operation(
                tags: ['Locale'],
                summary: 'List store locales',
                description: 'Returns all active store locales. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'List of locales.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 1,
                                        'code' => 'en',
                                        'name' => 'English',
                                        'direction' => 'ltr',
                                        'logoPath' => 'locales/en.png',
                                        'createdAt' => null,
                                        'updatedAt' => null,
                                        'logoUrl' => 'http://localhost:8000/storage/locales/en.png',
                                    ], [
                                        'id' => 10,
                                        'code' => 'AR',
                                        'name' => 'Arabic',
                                        'direction' => 'rtl',
                                        'logoPath' => 'locales/AR.png',
                                        'createdAt' => '2026-04-02T23:21:21+05:30',
                                        'updatedAt' => '2026-04-02T23:21:21+05:30',
                                        'logoUrl' => 'http://localhost:8000/storage/locales/AR.png',
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
            resolver: BaseQueryItemResolver::class,
            description: 'One locale. Pass the IRI or the numeric ID as `id`; an unknown ID is an error. Needs the X-STOREFRONT-KEY header.',
        ),
        new QueryCollection(
            provider: CursorAwareCollectionProvider::class,
            description: 'Every locale set up in the store, ordered by ID; the current channel\'s locales are on the channel. Cursor pagination: `first` takes the first N rows (after `after` when given), `last` without `before` takes the last N, and `before` takes the N rows before that cursor; N defaults to 10 and is capped at 50, and `first: 0` returns an empty page. Needs the X-STOREFRONT-KEY header.',
        ),
    ]
)
]
class Locale extends \Webkul\Core\Models\Locale
{
    /**
     * The logo path.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $logoPath = null;

    /**
     * The logo url.
     */
    #[ApiProperty(writable: false, readable: true)]
    public ?string $logoUrl = null;

    /**
     * Get id.
     */
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }
}

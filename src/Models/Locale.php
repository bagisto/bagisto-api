<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use Webkul\BagistoApi\Resolver\BaseQueryItemResolver;
use Webkul\BagistoApi\State\CursorAwareCollectionProvider;

#[ApiResource(
    routePrefix: '/api/shop',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new Get,
        new GetCollection(paginationClientItemsPerPage: true),
    ],
    graphQlOperations: [
        new Query(resolver: BaseQueryItemResolver::class),
        new QueryCollection(provider: CursorAwareCollectionProvider::class),
    ]
)
]
class Locale extends \Webkul\Core\Models\Locale
{
    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false, readable: true)]
    public ?string $logoPath = null;

    #[ApiProperty(writable: false, readable: true)]
    public ?string $logoUrl = null;
}

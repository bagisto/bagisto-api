<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingProductsQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminReportingProductsProvider;

/**
 * Admin reporting — products (read-only).
 *
 * REST   : GET /api/admin/reporting/products
 * GraphQL: adminReportingProducts query
 *
 * Mirrors `Reporting/ProductController::stats()`. `?type=`:
 *   total-sold-quantities (default), total-products-added-to-wishlist,
 *   top-selling-products-by-revenue, top-selling-products-by-quantity,
 *   products-with-most-reviews, products-with-most-visits,
 *   last-search-terms, top-search-terms.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminReportingProducts',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/reporting/products',
            provider: AdminReportingProductsProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Reporting'],
                summary: 'Reporting — products',
                description: 'Product reporting stats. `?type=` picks the stat group.',
                parameters: [
                    new Model\Parameter('type', 'query', 'Stat group.', false, schema: ['type' => 'string', 'enum' => ['total-sold-quantities', 'total-products-added-to-wishlist', 'top-selling-products-by-revenue', 'top-selling-products-by-quantity', 'products-with-most-reviews', 'products-with-most-visits', 'last-search-terms', 'top-search-terms']]),
                    new Model\Parameter('start', 'query', 'Start date.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('end', 'query', 'End date.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('channel', 'query', 'Channel code.', false, schema: ['type' => 'string']),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'stats',
            resolver: AdminReportingProductsQueryResolver::class,
            args: [
                'type'    => ['type' => 'String'],
                'start'   => ['type' => 'String'],
                'end'     => ['type' => 'String'],
                'channel' => ['type' => 'String'],
            ],
            normalizationContext: ['groups' => ['query']],
            description: 'Product reporting stats.',
        ),
    ],
)]
class AdminReportingProducts
{
    #[ApiProperty(readable: true, writable: false, identifier: true)]
    #[Groups(['query'])]
    public ?string $entity = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $type = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?array $dateRange = null;

    /** @var array<string,mixed>|null */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?array $statistics = null;
}

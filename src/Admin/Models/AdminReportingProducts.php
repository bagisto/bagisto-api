<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingProductsQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingProductsViewResolver;
use Webkul\BagistoApi\Admin\State\AdminReportingProductsExportProvider;
use Webkul\BagistoApi\Admin\State\AdminReportingProductsProvider;
use Webkul\BagistoApi\Admin\State\AdminReportingProductsViewProvider;

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
                tags: ['Admin Reporting: Products'],
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
        new GetCollection(
            uriTemplate: '/reporting/products/view',
            provider: AdminReportingProductsViewProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Reporting: Products'],
                summary: 'Reporting — products (View Details)',
                description: 'The detailed table form of a product stat (the admin "View Details" page). `statistics` is `{ columns, records }`. `?type=` picks the stat group.',
                parameters: [
                    new Model\Parameter('type', 'query', 'Stat group.', false, schema: ['type' => 'string', 'enum' => ['total-sold-quantities', 'total-products-added-to-wishlist', 'top-selling-products-by-revenue', 'top-selling-products-by-quantity', 'products-with-most-reviews', 'products-with-most-visits', 'last-search-terms', 'top-search-terms']]),
                    new Model\Parameter('start', 'query', 'Start date.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('end', 'query', 'End date.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('channel', 'query', 'Channel code.', false, schema: ['type' => 'string']),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/reporting/products/export',
            provider: AdminReportingProductsExportProvider::class,
            paginationEnabled: false,
            outputFormats: ['csv' => ['text/csv']],
            openapi: new Model\Operation(
                tags: ['Admin Reporting: Products'],
                summary: 'Reporting — products export (CSV)',
                description: 'Streams a product stat as a CSV download (the admin Export button). REST only; send Accept: text/csv. `?type=` picks the stat group; `?format=` accepts only csv.',
                parameters: [
                    new Model\Parameter('type', 'query', 'Stat group.', false, schema: ['type' => 'string']),
                    new Model\Parameter('format', 'query', 'Export format (only csv).', false, schema: ['type' => 'string', 'enum' => ['csv'], 'example' => 'csv']),
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
                'type' => ['type' => 'String'],
                'start' => ['type' => 'String'],
                'end' => ['type' => 'String'],
                'channel' => ['type' => 'String'],
            ],
            normalizationContext: ['groups' => ['query']],
            description: 'Product reporting stats.',
        ),
        new Query(
            name: 'viewStats',
            resolver: AdminReportingProductsViewResolver::class,
            args: [
                'type' => ['type' => 'String'],
                'start' => ['type' => 'String'],
                'end' => ['type' => 'String'],
                'channel' => ['type' => 'String'],
            ],
            normalizationContext: ['groups' => ['query']],
            description: 'Product reporting — View Details (table form: { columns, records }).',
        ),
    ],
)]
class AdminReportingProducts
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(readable: true, writable: false, identifier: true)]
    #[Groups(['query'])]
    public ?string $entity = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $type = null;

    #[ApiProperty(readable: true, writable: false, example: ['previous' => '10 Apr 2026 - 10 May 2026', 'current' => '10 May 2026 - 09 Jun 2026'])]
    #[Groups(['query'])]
    public ?array $date_range = null;

    /** @var array<string,mixed>|null */
    #[ApiProperty(readable: true, writable: false, example: ['quantities' => ['previous' => 82, 'current' => 13, 'progress' => -84.15], 'over_time' => ['previous' => [['label' => '10 Apr', 'total' => 21]], 'current' => [['label' => '10 May', 'total' => 5]]]])]
    #[Groups(['query'])]
    public ?array $statistics = null;
}

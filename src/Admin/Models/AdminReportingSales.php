<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingSalesQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingSalesViewResolver;
use Webkul\BagistoApi\Admin\State\AdminReportingSalesExportProvider;
use Webkul\BagistoApi\Admin\State\AdminReportingSalesProvider;
use Webkul\BagistoApi\Admin\State\AdminReportingSalesViewProvider;

/**
 * Admin reporting — sales (read-only).
 *
 * REST   : GET /api/admin/reporting/sales
 * GraphQL: adminReportingSales query
 *
 * Mirrors `Reporting/SaleController::stats()`. `?type=`:
 *   total-sales (default), average-sales, total-orders, purchase-funnel,
 *   abandoned-carts, refunds, tax-collected, shipping-collected,
 *   top-payment-methods, sales-by-coupon.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminReportingSales',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/reporting/sales',
            provider: AdminReportingSalesProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Reporting: Sales'],
                summary: 'Reporting — sales',
                description: 'Sales reporting stats. `?type=` chooses the stat group.',
                parameters: [
                    new Model\Parameter('type', 'query', 'Stat group.', false, schema: ['type' => 'string', 'enum' => ['total-sales', 'average-sales', 'total-orders', 'purchase-funnel', 'abandoned-carts', 'refunds', 'tax-collected', 'shipping-collected', 'top-payment-methods', 'sales-by-coupon']]),
                    new Model\Parameter('start', 'query', 'Start date.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('end', 'query', 'End date.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('channel', 'query', 'Channel code.', false, schema: ['type' => 'string']),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/reporting/sales/view',
            provider: AdminReportingSalesViewProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Reporting: Sales'],
                summary: 'Reporting — sales (View Details)',
                description: 'The detailed table form of a sales stat (the admin "View Details" page). `statistics` is `{ columns, records }`. `?type=` chooses the stat group.',
                parameters: [
                    new Model\Parameter('type', 'query', 'Stat group.', false, schema: ['type' => 'string', 'enum' => ['total-sales', 'average-sales', 'total-orders', 'purchase-funnel', 'abandoned-carts', 'refunds', 'tax-collected', 'shipping-collected', 'top-payment-methods', 'sales-by-coupon']]),
                    new Model\Parameter('start', 'query', 'Start date.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('end', 'query', 'End date.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('channel', 'query', 'Channel code.', false, schema: ['type' => 'string']),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/reporting/sales/export',
            provider: AdminReportingSalesExportProvider::class,
            paginationEnabled: false,
            outputFormats: ['csv' => ['text/csv']],
            openapi: new Model\Operation(
                tags: ['Admin Reporting: Sales'],
                summary: 'Reporting — sales export (CSV)',
                description: 'Streams a sales stat as a CSV download (the admin Export button). REST only; send Accept: text/csv. `?type=` chooses the stat group; `?format=` accepts only csv.',
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
            resolver: AdminReportingSalesQueryResolver::class,
            args: [
                'type'    => ['type' => 'String'],
                'start'   => ['type' => 'String'],
                'end'     => ['type' => 'String'],
                'channel' => ['type' => 'String'],
            ],
            normalizationContext: ['groups' => ['query']],
            description: 'Sales reporting stats.',
        ),
        new Query(
            name: 'viewStats',
            resolver: AdminReportingSalesViewResolver::class,
            args: [
                'type'    => ['type' => 'String'],
                'start'   => ['type' => 'String'],
                'end'     => ['type' => 'String'],
                'channel' => ['type' => 'String'],
            ],
            normalizationContext: ['groups' => ['query']],
            description: 'Sales reporting — View Details (table form: { columns, records }).',
        ),
    ],
)]
class AdminReportingSales
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
    #[ApiProperty(readable: true, writable: false, example: ['sales' => ['previous' => 27178.38, 'current' => 9697.53, 'formatted_total' => '$9,697.53', 'progress' => -64.32], 'over_time' => ['previous' => [['label' => '10 Apr', 'total' => '8526.82', 'count' => 12]], 'current' => [['label' => '10 May', 'total' => '8500.00', 'count' => 12]]]])]
    #[Groups(['query'])]
    public ?array $statistics = null;
}

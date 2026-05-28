<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingOverviewQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminReportingOverviewProvider;

/**
 * Admin reporting overview (read-only).
 *
 * REST   : GET /api/admin/reporting/stats
 * GraphQL: adminReportingStats query
 *
 * Mirrors `Webkul\Admin\Http\Controllers\Reporting\Controller::stats()`.
 * The `?type=` query param picks one of the overview stat groups:
 *   total-sales (default), total-orders, total-customers,
 *   top-selling-products-by-revenue.
 *
 * `start` / `end` (ISO) + `channel` (code) bound the period.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminReportingOverview',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/reporting/stats',
            provider: AdminReportingOverviewProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Reporting'],
                summary: 'Reporting — overview',
                description: 'Aggregate headline stats across sales/customers/products. Use `?type=` for the stat group; `?start=`, `?end=`, `?channel=` to bound the period.',
                parameters: [
                    new Model\Parameter('type', 'query', 'Stat group: total-sales (default), total-orders, total-customers, top-selling-products-by-revenue.', false, schema: ['type' => 'string']),
                    new Model\Parameter('start', 'query', 'Start date (YYYY-MM-DD).', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('end', 'query', 'End date (YYYY-MM-DD).', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('channel', 'query', 'Channel code.', false, schema: ['type' => 'string']),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'stats',
            resolver: AdminReportingOverviewQueryResolver::class,
            args: [
                'type'    => ['type' => 'String'],
                'start'   => ['type' => 'String'],
                'end'     => ['type' => 'String'],
                'channel' => ['type' => 'String'],
            ],
            normalizationContext: ['groups' => ['query']],
            description: 'Reporting overview — aggregate headline stats. `type` picks the stat group, `start`/`end` (ISO) + `channel` (code) bound the period.',
        ),
    ],
)]
class AdminReportingOverview
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

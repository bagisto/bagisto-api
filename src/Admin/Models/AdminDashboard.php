<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\Resolver\AdminDashboardQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminDashboardProvider;

/**
 * Admin dashboard statistics (read-only).
 *
 * REST   : GET /api/admin/dashboard/stats
 * GraphQL: adminDashboardStats query
 *
 * Mirrors `Webkul\Admin\Http\Controllers\DashboardController::stats()`.
 * The `type` query param selects which named stat group is returned by
 * the `Webkul\Admin\Helpers\Dashboard` helper:
 *
 *   over-all (default), today, stock-threshold-products, total-sales,
 *   total-visitors, top-selling-products, top-customers
 *
 * `start` / `end` (ISO dates) and `channel` (channel code) narrow the
 * window; the helper falls back to "last 30 days" when omitted.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminDashboard',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/dashboard/stats',
            provider: AdminDashboardProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Dashboard'],
                summary: 'Admin dashboard statistics',
                description: 'Returns aggregate stats from `Webkul\\Admin\\Helpers\\Dashboard`. Use `?type=` to choose the stat group; `?start=` + `?end=` (ISO dates) to bound the window; `?channel=` (channel code) to filter by channel.',
                parameters: [
                    new Model\Parameter('type', 'query', 'Stat group: over-all (default), today, stock-threshold-products, total-sales, total-visitors, top-selling-products, top-customers.', false, schema: ['type' => 'string', 'enum' => ['over-all', 'today', 'stock-threshold-products', 'total-sales', 'total-visitors', 'top-selling-products', 'top-customers']]),
                    new Model\Parameter('start', 'query', 'Start date (YYYY-MM-DD). Defaults to 30 days ago.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('end', 'query', 'End date (YYYY-MM-DD). Defaults to today.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('channel', 'query', 'Channel code filter.', false, schema: ['type' => 'string']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Stats payload + date range.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [[
                                    'type' => 'over-all',
                                    'dateRange' => '25 Apr - 25 May',
                                    'statistics' => [
                                        'total_customers' => ['previous' => 12, 'current' => 18, 'progress' => 50],
                                        'total_orders' => ['previous' => 32, 'current' => 41, 'progress' => 28.13],
                                        'total_sales' => ['previous' => ['price' => 1200], 'current' => ['price' => 1900], 'progress' => 58.33],
                                        'avg_sales' => ['previous' => ['price' => 37.5], 'current' => ['price' => 46.3], 'progress' => 23.46],
                                        'total_unpaid_invoices' => ['total' => 250, 'formatted_total' => '$250.00'],
                                    ],
                                ]],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            name: 'stats',
            resolver: AdminDashboardQueryResolver::class,
            args: [
                'type' => ['type' => 'String'],
                'start' => ['type' => 'String'],
                'end' => ['type' => 'String'],
                'channel' => ['type' => 'String'],
            ],
            normalizationContext: ['groups' => ['query']],
            description: 'Admin dashboard statistics. Pass `type` to choose the stat group (default: over-all). `start` / `end` (ISO dates) and `channel` (code) narrow the window.',
        ),
    ],
)]
class AdminDashboard
{
    use AcceptsCamelCaseWrites;

    #[ApiProperty(readable: true, writable: false, identifier: true)]
    #[Groups(['query'])]
    public ?string $type = null;

    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?string $date_range = null;

    /**
     * @var array<string,mixed>|null
     */
    #[ApiProperty(readable: true, writable: false)]
    #[Groups(['query'])]
    public ?array $statistics = null;
}

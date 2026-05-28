<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingSalesQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminReportingSalesProvider;

/**
 * Admin reporting — sales (read-only).
 *
 * REST   : GET /api/admin/reporting/sales
 * GraphQL: adminReportingSales query
 *
 * Mirrors `Reporting/SaleController::stats()`. `?type=`:
 *   total-sales (default), average-sales, total-orders, purchase-funnel,
 *   abandoned-carts, refunds, tax-collected, shipping-collected,
 *   top-payment-methods.
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
                tags: ['Admin Reporting'],
                summary: 'Reporting — sales',
                description: 'Sales reporting stats. `?type=` chooses the stat group.',
                parameters: [
                    new Model\Parameter('type', 'query', 'Stat group.', false, schema: ['type' => 'string', 'enum' => ['total-sales', 'average-sales', 'total-orders', 'purchase-funnel', 'abandoned-carts', 'refunds', 'tax-collected', 'shipping-collected', 'top-payment-methods']]),
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
    ],
)]
class AdminReportingSales
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

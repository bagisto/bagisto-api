<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingCustomersQueryResolver;
use Webkul\BagistoApi\Admin\State\AdminReportingCustomersProvider;

/**
 * Admin reporting — customers (read-only).
 *
 * REST   : GET /api/admin/reporting/customers
 * GraphQL: adminReportingCustomers query
 *
 * Mirrors `Reporting/CustomerController::stats()`. `?type=`:
 *   total-customers (default), customers-traffic, customers-with-most-sales,
 *   customers-with-most-orders, customers-with-most-reviews,
 *   top-customer-groups.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminReportingCustomers',
    paginationEnabled: false,
    operations: [
        new GetCollection(
            uriTemplate: '/reporting/customers',
            provider: AdminReportingCustomersProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Reporting'],
                summary: 'Reporting — customers',
                description: 'Customer reporting stats. `?type=` picks the stat group.',
                parameters: [
                    new Model\Parameter('type', 'query', 'Stat group.', false, schema: ['type' => 'string', 'enum' => ['total-customers', 'customers-traffic', 'customers-with-most-sales', 'customers-with-most-orders', 'customers-with-most-reviews', 'top-customer-groups']]),
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
            resolver: AdminReportingCustomersQueryResolver::class,
            args: [
                'type'    => ['type' => 'String'],
                'start'   => ['type' => 'String'],
                'end'     => ['type' => 'String'],
                'channel' => ['type' => 'String'],
            ],
            normalizationContext: ['groups' => ['query']],
            description: 'Customer reporting stats.',
        ),
    ],
)]
class AdminReportingCustomers
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

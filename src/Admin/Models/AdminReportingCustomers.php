<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\Serializer\Annotation\Groups;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingCustomersQueryResolver;
use Webkul\BagistoApi\Admin\Resolver\AdminReportingCustomersViewResolver;
use Webkul\BagistoApi\Admin\State\AdminReportingCustomersExportProvider;
use Webkul\BagistoApi\Admin\State\AdminReportingCustomersProvider;
use Webkul\BagistoApi\Admin\State\AdminReportingCustomersViewProvider;

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
                tags: ['Admin Reporting: Customers'],
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
        new GetCollection(
            uriTemplate: '/reporting/customers/view',
            provider: AdminReportingCustomersViewProvider::class,
            paginationEnabled: false,
            normalizationContext: ['skip_null_values' => false],
            openapi: new Model\Operation(
                tags: ['Admin Reporting: Customers'],
                summary: 'Reporting — customers (View Details)',
                description: 'The detailed table form of a customer stat (the admin "View Details" page). `statistics` is `{ columns, records }`. `?type=` picks the stat group.',
                parameters: [
                    new Model\Parameter('type', 'query', 'Stat group.', false, schema: ['type' => 'string', 'enum' => ['total-customers', 'customers-traffic', 'customers-with-most-sales', 'customers-with-most-orders', 'customers-with-most-reviews', 'top-customer-groups']]),
                    new Model\Parameter('start', 'query', 'Start date.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('end', 'query', 'End date.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('channel', 'query', 'Channel code.', false, schema: ['type' => 'string']),
                ],
            ),
        ),
        new GetCollection(
            uriTemplate: '/reporting/customers/export',
            provider: AdminReportingCustomersExportProvider::class,
            paginationEnabled: false,
            outputFormats: ['csv' => ['text/csv']],
            openapi: new Model\Operation(
                tags: ['Admin Reporting: Customers'],
                summary: 'Reporting — customers export (CSV)',
                description: 'Streams a customer stat as a CSV download (the admin Export button). REST only; send Accept: text/csv. `?type=` picks the stat group; `?format=` accepts only csv.',
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
        new Query(
            name: 'viewStats',
            resolver: AdminReportingCustomersViewResolver::class,
            args: [
                'type'    => ['type' => 'String'],
                'start'   => ['type' => 'String'],
                'end'     => ['type' => 'String'],
                'channel' => ['type' => 'String'],
            ],
            normalizationContext: ['groups' => ['query']],
            description: 'Customer reporting — View Details (table form: { columns, records }).',
        ),
    ],
)]
class AdminReportingCustomers
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
    #[ApiProperty(readable: true, writable: false, example: ['customers' => ['previous' => 1, 'current' => 9, 'progress' => 800], 'over_time' => ['previous' => [['label' => '23 May', 'total' => 1]], 'current' => [['label' => '26 May', 'total' => 9]]]])]
    #[Groups(['query'])]
    public ?array $statistics = null;
}

<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminInvoiceCreateInput;
use Webkul\BagistoApi\Admin\Dto\AdminInvoiceDetailDto;
use Webkul\BagistoApi\Admin\State\AdminInvoiceCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminInvoiceCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminInvoicePrintProvider;
use Webkul\BagistoApi\Admin\State\AdminInvoiceProvider;

/**
 * Admin Invoice resource.
 *
 * REST  : GET  /api/admin/invoices                   (datagrid listing)
 *         POST /api/admin/orders/{orderId}/invoices  (create)
 *         GET  /api/admin/invoices/{id}              (view)
 *         GET  /api/admin/invoices/{id}/print        (download PDF)
 * GraphQL: adminInvoices (cursor), adminInvoice query, createAdminInvoice mutation.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminInvoice',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/invoices',
            provider: AdminInvoiceCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales'],
                summary: 'List invoices (datagrid parity)',
                description: 'Paginated invoices listing mirroring the admin Sales → Invoices datagrid. Returns a `{ data, meta }` envelope. Requires `sales.invoices.view` permission.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('id', 'query', 'Filter by invoice id (integer or comma-separated list).', false, schema: ['type' => 'string', 'example' => '12']),
                    new Model\Parameter('order_id', 'query', 'Partial order increment_id match.', false, schema: ['type' => 'string', 'example' => '00000']),
                    new Model\Parameter('state', 'query', 'Filter by invoice state.', false, schema: ['type' => 'string', 'enum' => ['pending', 'pending_payment', 'paid', 'overdue', 'refunded'], 'example' => 'paid']),
                    new Model\Parameter('base_grand_total_from', 'query', 'Minimum base grand total.', false, schema: ['type' => 'number', 'example' => 0]),
                    new Model\Parameter('base_grand_total_to', 'query', 'Maximum base grand total.', false, schema: ['type' => 'number', 'example' => 1000]),
                    new Model\Parameter('created_at_from', 'query', 'Created after (ISO date).', false, schema: ['type' => 'string', 'format' => 'date', 'example' => '2026-01-01']),
                    new Model\Parameter('created_at_to', 'query', 'Created before (ISO date).', false, schema: ['type' => 'string', 'format' => 'date', 'example' => '2026-12-31']),
                    new Model\Parameter('sort', 'query', 'Column to sort by.', false, schema: ['type' => 'string', 'enum' => ['id', 'increment_id', 'order_id', 'base_grand_total', 'state', 'created_at'], 'example' => 'id']),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'desc']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list of invoices in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [[
                                        'id'                      => 12,
                                        'incrementId'             => '12',
                                        'orderId'                 => 8,
                                        'orderIncrementId'        => '00000000008',
                                        'state'                   => 'paid',
                                        'baseGrandTotal'          => 99.99,
                                        'formattedBaseGrandTotal' => '$99.99',
                                        'createdAt'               => '2026-05-20 12:34:56',
                                    ]],
                                    'meta' => [
                                        'currentPage' => 1, 'perPage' => 10, 'lastPage' => 1, 'total' => 1, 'from' => 1, 'to' => 1,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/invoices/{id}',
            requirements: ['id' => '\\d+'],
            provider: AdminInvoiceProvider::class,
            output: AdminInvoiceDetailDto::class,
            openapi: new Model\Operation(
                tags: ['Admin Order Actions'],
                summary: 'Get invoice detail',
                parameters: [
                    new Model\Parameter('id', 'path', 'Invoice ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/invoices/{id}/print',
            requirements: ['id' => '\\d+'],
            provider: AdminInvoicePrintProvider::class,
            outputFormats: ['pdf' => ['application/pdf']],
            openapi: new Model\Operation(
                tags: ['Admin Order Actions'],
                summary: 'Download an invoice as PDF',
                parameters: [
                    new Model\Parameter('id', 'path', 'Invoice ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'application/pdf binary'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/orders/{orderId}/invoices',
            input: AdminInvoiceCreateInput::class,
            output: AdminInvoiceDetailDto::class,
            processor: AdminInvoiceCreateProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Order Actions'],
                summary: 'Create an invoice for an order',
                parameters: [
                    new Model\Parameter('orderId', 'path', 'Order ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema'  => ['type' => 'object'],
                            'example' => [
                                'items' => [
                                    ['orderItemId' => 42, 'quantity' => 3],
                                    ['orderItemId' => 43, 'quantity' => 1],
                                ],
                            ],
                        ],
                    ]),
                ),
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            provider: AdminInvoiceProvider::class,
            output: AdminInvoiceDetailDto::class,
            description: 'Get an invoice by id.',
        ),
        new QueryCollection(
            provider: AdminInvoiceCollectionProvider::class,
            paginationType: 'cursor',
            description: 'Admin invoices datagrid listing (cursor pagination).',
            extraArgs: [
                'id'                    => ['type' => 'String'],
                'order_id'              => ['type' => 'String'],
                'state'                 => ['type' => 'String'],
                'base_grand_total_from' => ['type' => 'Float'],
                'base_grand_total_to'   => ['type' => 'Float'],
                'created_at_from'       => ['type' => 'String'],
                'created_at_to'         => ['type' => 'String'],
                'date_range'            => ['type' => 'String'],
                'sort'                  => ['type' => 'String'],
                'order'                 => ['type' => 'String'],
            ],
        ),
        new Mutation(
            name: 'create',
            input: AdminInvoiceCreateInput::class,
            output: AdminInvoiceDetailDto::class,
            processor: AdminInvoiceCreateProcessor::class,
            description: 'Create an invoice on an order.',
        ),
    ],
)]
class AdminInvoice
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;
}

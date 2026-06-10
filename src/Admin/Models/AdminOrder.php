<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\OrderItemPreview;
use Webkul\BagistoApi\Admin\State\AdminOrderExportProvider;
use Webkul\BagistoApi\Admin\State\OrderCollectionProvider;

/**
 * Admin Orders listing — one slim order row.
 *
 * REST  : GET /api/admin/orders → `{ data: [AdminOrder], meta: {...} }`
 *         (the `data`/`meta` envelope is applied by AdminCollectionEnvelopeNormalizer).
 * GraphQL: adminOrders query → native cursor pagination (edges + pageInfo).
 *
 * Only flat order fields + a light `items` preview. Heavy relations
 * (full items, invoices, shipments) are served by sub-resources.
 */
#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminOrder',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/orders',
            provider: OrderCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'List orders',
                description: 'Paginated, filterable list of all orders across every customer. Returns a slim row per order in a `{ data, meta }` envelope; use the order detail and sub-resources for items / invoices / shipments.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (max 50)', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('order_id', 'query', 'Filter by order increment ID (partial match)', false, schema: ['type' => 'string']),
                    new Model\Parameter('status', 'query', 'Filter by status', false, schema: ['type' => 'string', 'enum' => ['pending', 'pending_payment', 'processing', 'completed', 'canceled', 'closed', 'fraud']]),
                    new Model\Parameter('grand_total', 'query', 'Filter by base grand total (exact)', false, schema: ['type' => 'number']),
                    new Model\Parameter('grand_total_from', 'query', 'Filter by base grand total (minimum)', false, schema: ['type' => 'number']),
                    new Model\Parameter('grand_total_to', 'query', 'Filter by base grand total (maximum)', false, schema: ['type' => 'number']),
                    new Model\Parameter('channel', 'query', 'Filter by channel ID', false, schema: ['type' => 'integer']),
                    new Model\Parameter('customer', 'query', 'Filter by customer name (partial match)', false, schema: ['type' => 'string']),
                    new Model\Parameter('email', 'query', 'Filter by customer email (partial match)', false, schema: ['type' => 'string']),
                    new Model\Parameter('date_range', 'query', 'Date preset (matches the admin datagrid)', false, schema: ['type' => 'string', 'enum' => ['today', 'yesterday', 'this_week', 'this_month', 'last_month', 'last_three_months', 'last_six_months', 'this_year']]),
                    new Model\Parameter('date_from', 'query', 'Custom range start (Y-m-d)', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('date_to', 'query', 'Custom range end (Y-m-d)', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('sort', 'query', 'Sort field', false, schema: ['type' => 'string', 'example' => 'created_at']),
                    new Model\Parameter('order', 'query', 'Sort direction', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc'], 'example' => 'desc']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list of orders in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id'                  => 2392,
                                            'incrementId'         => '2392',
                                            'status'              => 'processing',
                                            'statusLabel'         => 'Processing',
                                            'channelId'           => 1,
                                            'channelName'         => 'bagisto store',
                                            'isGuest'             => false,
                                            'customerId'          => 19,
                                            'customerEmail'       => 'admin@example.com',
                                            'customerName'        => 'Test User',
                                            'paymentTitle'        => 'Money Transfer',
                                            'couponCode'          => null,
                                            'totalItemCount'      => 1,
                                            'totalQtyOrdered'     => 1,
                                            'orderCurrencyCode'   => 'USD',
                                            'grandTotal'          => 4000,
                                            'baseGrandTotal'      => 4000,
                                            'formattedGrandTotal' => '$4,000.00',
                                            'location'            => 'New York, NY, US',
                                            'createdAt'           => '2026-05-19 13:13:29',
                                            'updatedAt'           => '2026-05-19 13:13:30',
                                            'items'               => [
                                                [
                                                    'id'           => 2694,
                                                    'sku'          => 'test65',
                                                    'name'         => 'Classic Watch Hand',
                                                    'qtyOrdered'   => 1,
                                                    'productImage' => 'http://localhost:8000/storage/product/2358/example.webp',
                                                ],
                                            ],
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage'     => 10,
                                        'lastPage'    => 62,
                                        'total'       => 616,
                                        'from'        => 1,
                                        'to'          => 10,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/orders/export',
            provider: AdminOrderExportProvider::class,
            outputFormats: ['csv' => ['text/csv']],
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Export orders as CSV',
                description: 'Downloads the orders datagrid as a CSV file (text/csv attachment) — the same data the admin Export button produces. Honours the same filters as the listing. Binary download, not JSON. Only ?format=csv is supported.',
                parameters: [
                    new Model\Parameter('format', 'query', 'Export format. Currently only csv.', false, schema: ['type' => 'string', 'enum' => ['csv'], 'default' => 'csv']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'CSV file downloaded (text/csv attachment).', content: new \ArrayObject(['text/csv' => ['schema' => ['type' => 'string', 'format' => 'binary']]])),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks the view permission.'),
                    '422' => new Model\Response(description: 'Unsupported format (only csv).'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: OrderCollectionProvider::class,
            paginationType: 'cursor',
            description: 'Paginated list of all orders (cursor pagination).',
            extraArgs: [
                'order_id'         => ['type' => 'String'],
                'status'           => ['type' => 'String'],
                'grand_total'      => ['type' => 'Float'],
                'grand_total_from' => ['type' => 'Float'],
                'grand_total_to'   => ['type' => 'Float'],
                'channel'          => ['type' => 'Int'],
                'customer'         => ['type' => 'String'],
                'email'            => ['type' => 'String'],
                'date_range'       => ['type' => 'String'],
                'date_from'        => ['type' => 'String'],
                'date_to'          => ['type' => 'String'],
                'sort'             => ['type' => 'String'],
                'order'            => ['type' => 'String'],
            ],
        ),
    ]
)]
class AdminOrder
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    #[ApiProperty(writable: false)]
    public ?string $increment_id = null;

    #[ApiProperty(writable: false)]
    public ?string $status = null;

    #[ApiProperty(writable: false)]
    public ?string $status_label = null;

    #[ApiProperty(writable: false)]
    public ?int $channel_id = null;

    #[ApiProperty(writable: false)]
    public ?string $channel_name = null;

    #[ApiProperty(writable: false)]
    public ?bool $is_guest = null;

    #[ApiProperty(writable: false)]
    public ?int $customer_id = null;

    #[ApiProperty(writable: false)]
    public ?string $customer_email = null;

    #[ApiProperty(writable: false)]
    public ?string $customer_name = null;

    #[ApiProperty(writable: false)]
    public ?string $payment_title = null;

    #[ApiProperty(writable: false)]
    public ?string $coupon_code = null;

    #[ApiProperty(writable: false)]
    public ?int $total_item_count = null;

    #[ApiProperty(writable: false)]
    public ?int $total_qty_ordered = null;

    #[ApiProperty(writable: false)]
    public ?string $order_currency_code = null;

    #[ApiProperty(writable: false)]
    public ?float $grand_total = null;

    #[ApiProperty(writable: false)]
    public ?float $base_grand_total = null;

    #[ApiProperty(writable: false)]
    public ?string $formatted_grand_total = null;

    #[ApiProperty(writable: false)]
    public ?string $location = null;

    #[ApiProperty(writable: false)]
    public ?string $created_at = null;

    #[ApiProperty(writable: false)]
    public ?string $updated_at = null;

    /**
     * Inlined as plain associative arrays (not OrderItemPreview instances)
     * to avoid API Platform's IRI-serialization trap for nested resources.
     *
     * @var array<int, array<string, mixed>>
     */
    #[ApiProperty(writable: false)]
    public array $items = [];
}

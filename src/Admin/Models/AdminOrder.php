<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\BagistoApi\Admin\Dto\AdminOrderListDto;
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
            output: AdminOrderListDto::class,
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
                                            'id' => 2392,
                                            'incrementId' => '2392',
                                            'status' => 'processing',
                                            'statusLabel' => 'Processing',
                                            'channelId' => 1,
                                            'channelName' => 'bagisto store',
                                            'isGuest' => false,
                                            'customerId' => 19,
                                            'customerEmail' => 'admin@example.com',
                                            'customerName' => 'Test User',
                                            'paymentTitle' => 'Money Transfer',
                                            'couponCode' => null,
                                            'totalItemCount' => 1,
                                            'totalQtyOrdered' => 1,
                                            'orderCurrencyCode' => 'USD',
                                            'grandTotal' => 4000,
                                            'baseGrandTotal' => 4000,
                                            'formattedGrandTotal' => '$4,000.00',
                                            'location' => 'New York, NY, US',
                                            'createdAt' => '2026-05-19 13:13:29',
                                            'updatedAt' => '2026-05-19 13:13:30',
                                            'items' => [
                                                [
                                                    'id' => 2694,
                                                    'sku' => 'test65',
                                                    'name' => 'Classic Watch Hand',
                                                    'qtyOrdered' => 1,
                                                    'productImage' => 'http://localhost:8000/storage/product/2358/example.webp',
                                                ],
                                            ],
                                        ],
                                    ],
                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage' => 10,
                                        'lastPage' => 62,
                                        'total' => 616,
                                        'from' => 1,
                                        'to' => 10,
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
            outputFormats: ['csv' => ['text/csv'], 'xls' => ['application/vnd.ms-excel'], 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Export orders as csv, xls or xlsx',
                description: 'Downloads the orders datagrid as a csv, xls or xlsx file — the same data the admin Export button produces. Honours the same filters as the listing. Binary download, not JSON. Pick the file type with ?format=csv|xls|xlsx (default csv).',
                parameters: [
                    new Model\Parameter('format', 'query', 'Export format: csv, xls or xlsx. Defaults to csv.', false, schema: ['type' => 'string', 'enum' => ['csv', 'xls', 'xlsx'], 'default' => 'csv']),
                ],
                responses: [
                    '200' => new Model\Response(description: 'Export file downloaded as an attachment.', content: new \ArrayObject(['text/csv' => ['schema' => ['type' => 'string', 'format' => 'binary']], 'application/vnd.ms-excel' => ['schema' => ['type' => 'string', 'format' => 'binary']], 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['schema' => ['type' => 'string', 'format' => 'binary']]])),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks the view permission.'),
                    '422' => new Model\Response(description: 'Unsupported format (csv, xls and xlsx only).'),
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
                'order_id' => ['type' => 'String'],
                'status' => ['type' => 'String'],
                'grand_total' => ['type' => 'Float'],
                'grand_total_from' => ['type' => 'Float'],
                'grand_total_to' => ['type' => 'Float'],
                'channel' => ['type' => 'Int'],
                'customer' => ['type' => 'String'],
                'email' => ['type' => 'String'],
                'date_range' => ['type' => 'String'],
                'date_from' => ['type' => 'String'],
                'date_to' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
        ),
    ]
)]
class AdminOrder extends EloquentModel
{
    protected $table = 'orders';

    protected $appends = [
        'status_label', 'channel_name', 'customer_name', 'payment_title',
        'location', 'formatted_grand_total',
    ];

    protected $casts = [
        'id' => 'int',
        'channel_id' => 'int',
        'is_guest' => 'boolean',
        'customer_id' => 'int',
        'total_item_count' => 'int',
        'total_qty_ordered' => 'int',
        'grand_total' => 'float',
        'base_grand_total' => 'float',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[ApiProperty(writable: false)]
    public function items(): HasMany
    {
        return $this->hasMany(AdminOrderItemPreview::class, 'order_id');
    }

    #[ApiProperty(writable: false)]
    public function getStatusLabelAttribute(): ?string
    {
        return $this->attributes['status_label'] ?? null;
    }

    #[ApiProperty(writable: false)]
    public function getChannelNameAttribute(): ?string
    {
        return $this->attributes['channel_name'] ?? null;
    }

    #[ApiProperty(writable: false)]
    public function getCustomerNameAttribute(): ?string
    {
        return $this->attributes['customer_name'] ?? null;
    }

    #[ApiProperty(writable: false)]
    public function getPaymentTitleAttribute(): ?string
    {
        return $this->attributes['payment_title'] ?? null;
    }

    #[ApiProperty(writable: false)]
    public function getLocationAttribute(): ?string
    {
        return $this->attributes['location'] ?? null;
    }

    #[ApiProperty(writable: false)]
    public function getFormattedGrandTotalAttribute(): ?string
    {
        return $this->attributes['formatted_grand_total'] ?? null;
    }
}

<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\OrderItemPreview;
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
                tags: ['Admin Orders'],
                summary: 'List orders',
                description: 'Paginated, filterable list of all orders across every customer. Returns a slim row per order in a `{ data, meta }` envelope; use the order detail and sub-resources for items / invoices / shipments.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (max 50)', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('order_id', 'query', 'Filter by order increment ID (partial match)', false, schema: ['type' => 'string']),
                    new Model\Parameter('status', 'query', 'Filter by status', false, schema: ['type' => 'string', 'enum' => ['pending', 'pending_payment', 'processing', 'completed', 'canceled', 'closed', 'fraud']]),
                    new Model\Parameter('grand_total', 'query', 'Filter by grand total (exact)', false, schema: ['type' => 'number']),
                    new Model\Parameter('channel', 'query', 'Filter by channel ID', false, schema: ['type' => 'integer']),
                    new Model\Parameter('customer', 'query', 'Filter by customer name (partial match)', false, schema: ['type' => 'string']),
                    new Model\Parameter('email', 'query', 'Filter by customer email (partial match)', false, schema: ['type' => 'string']),
                    new Model\Parameter('date_range', 'query', 'Date preset', false, schema: ['type' => 'string', 'enum' => ['today', 'yesterday', 'this_week', 'this_month', 'last_month', 'last_3_months', 'last_6_months', 'this_year']]),
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
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: OrderCollectionProvider::class,
            paginationType: 'cursor',
            description: 'Paginated list of all orders (cursor pagination).',
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

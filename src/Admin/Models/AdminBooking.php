<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminBookingCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminBookingExportProvider;
use Webkul\BagistoApi\Admin\State\AdminBookingItemProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminBooking',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/bookings',
            provider: AdminBookingCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Bookings'],
                summary: 'List bookings (datagrid parity)',
                description: 'Paginated bookings listing mirroring the admin Sales → Bookings datagrid. Every booking column plus the linked order / order-item summary is populated on each row. Returns a `{ data, meta }` envelope. Requires `sales.bookings.view` permission.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('id', 'query', 'Filter by booking id (integer or comma-list).', false, schema: ['type' => 'string']),
                    new Model\Parameter('order_id', 'query', 'Partial order increment_id.', false, schema: ['type' => 'string']),
                    new Model\Parameter('qty', 'query', 'Exact quantity.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('product_id', 'query', 'Filter by product id.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('from_from', 'query', 'Slot start >= (ISO date).', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('from_to', 'query', 'Slot start <= (ISO date).', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('to_from', 'query', 'Slot end >= (ISO date).', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('to_to', 'query', 'Slot end <= (ISO date).', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('created_at_from', 'query', 'Order created after.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('created_at_to', 'query', 'Order created before.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'order_id', 'qty', 'from', 'to', 'created_at']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated bookings in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [self::SAMPLE],
                                    'meta' => ['currentPage' => 1, 'perPage' => 10, 'lastPage' => 1, 'total' => 1, 'from' => 1, 'to' => 1],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/bookings/{id}',
            requirements: ['id' => '\\d+'],
            provider: AdminBookingItemProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Bookings'],
                summary: 'Get a booking by id',
                description: 'Returns a single booking with its booking-product sub-type, the booking window, and the linked order / order-item summary — no follow-up calls required. Requires `sales.bookings.view` permission.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Booking row ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'The booking.',
                        content: new \ArrayObject([
                            'application/json' => ['example' => self::SAMPLE_DETAIL],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Unknown booking id.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks sales.bookings.view.'),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/bookings/export',
            provider: AdminBookingExportProvider::class,
            outputFormats: ['csv' => ['text/csv']],
            openapi: new Model\Operation(
                tags: ['Admin Sales: Bookings'],
                summary: 'Export bookings as CSV',
                description: 'Downloads the bookings datagrid as a CSV file (text/csv attachment) — the same data the admin Export button produces. Honours the same filters as the listing. Binary download, not JSON. Only ?format=csv is supported.',
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
        new Query(
            provider: AdminBookingItemProvider::class,
            description: 'Get a booking by id.',
        ),
        new QueryCollection(
            provider: AdminBookingCollectionProvider::class,
            paginationType: 'cursor',
            description: 'Admin bookings listing (cursor pagination).',
            extraArgs: [
                'id' => ['type' => 'String'],
                'order_id' => ['type' => 'String'],
                'qty' => ['type' => 'Int'],
                'product_id' => ['type' => 'Int'],
                'from_from' => ['type' => 'String'],
                'from_to' => ['type' => 'String'],
                'to_from' => ['type' => 'String'],
                'to_to' => ['type' => 'String'],
                'created_at_from' => ['type' => 'String'],
                'created_at_to' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
        ),
    ],
)]
class AdminBooking
{
    use AcceptsCamelCaseWrites;

    private const SAMPLE = [
        'id' => 1,
        'orderId' => 8,
        'orderIncrementId' => '00000000008',
        'orderItemId' => 42,
        'productId' => 99,
        'productSku' => 'BK-EVENT-01',
        'productName' => 'Concert Ticket',
        'bookingType' => 'event',
        'qty' => 2,
        'from' => 1716220800,
        'to' => 1716224400,
        'fromFormatted' => '20 May, 2026 12:00PM',
        'toFormatted' => '20 May, 2026 13:00PM',
        'bookingProductEventTicketId' => 5,
        'order' => [
            'id' => 8,
            'incrementId' => '00000000008',
            'status' => 'processing',
            'customerName' => 'John Doe',
            'customerEmail' => 'john.doe@example.com',
            'grandTotal' => 240,
            'orderCurrencyCode' => 'USD',
        ],
        'orderItem' => [
            'id' => 42,
            'sku' => 'BK-EVENT-01',
            'name' => 'Concert Ticket',
            'qtyOrdered' => 2,
        ],
        'createdAt' => '2026-05-20 10:00:00',
    ];

    private const SAMPLE_DETAIL = [
        'id' => 1,
        'orderId' => 8,
        'orderIncrementId' => '00000000008',
        'orderItemId' => 42,
        'productId' => 99,
        'productSku' => 'BK-EVENT-01',
        'productName' => 'Concert Ticket',
        'bookingType' => 'event',
        'qty' => 2,
        'from' => 1716220800,
        'to' => 1716224400,
        'fromFormatted' => '20 May, 2026 12:00PM',
        'toFormatted' => '20 May, 2026 13:00PM',
        'bookingProductEventTicketId' => 5,
        'order' => [
            'id' => 8,
            'incrementId' => '00000000008',
            'status' => 'processing',
            'customerName' => 'John Doe',
            'customerEmail' => 'john.doe@example.com',
            'grandTotal' => 240,
            'orderCurrencyCode' => 'USD',
        ],
        'orderItem' => [
            'id' => 42,
            'sku' => 'BK-EVENT-01',
            'name' => 'Concert Ticket',
            'qtyOrdered' => 2,
        ],
        'paymentMethod' => 'cashondelivery',
        'paymentTitle' => 'Cash On Delivery',
        'shippingMethod' => 'flatrate_flatrate',
        'shippingTitle' => 'Flat Rate - Flat Rate',
        'billingAddress' => [
            'id' => 16, 'addressType' => 'order_billing', 'firstName' => 'John', 'lastName' => 'Doe',
            'city' => 'Los Angeles', 'country' => 'US', 'postcode' => '90001', 'email' => 'john.doe@example.com', 'phone' => '5551234567',
        ],
        'shippingAddress' => [
            'id' => 15, 'addressType' => 'order_shipping', 'firstName' => 'John', 'lastName' => 'Doe',
            'city' => 'Los Angeles', 'country' => 'US', 'postcode' => '90001', 'email' => 'john.doe@example.com', 'phone' => '5551234567',
        ],
        'invoices' => [[
            'id' => 503, 'incrementId' => '503', 'state' => 'paid', 'baseGrandTotal' => 240,
            'formattedBaseGrandTotal' => '$240.00', 'createdAt' => '2026-05-20 12:01:00',
        ]],
        'shipments' => [],
        'refunds' => [],
        'createdAt' => '2026-05-20 10:00:00',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    public ?int $order_id = null;

    public ?string $order_increment_id = null;

    public ?int $order_item_id = null;

    public ?int $product_id = null;

    public ?string $product_sku = null;

    public ?string $product_name = null;

    public ?string $booking_type = null;

    public ?int $qty = null;

    public ?int $from = null;

    public ?int $to = null;

    public ?string $from_formatted = null;

    public ?string $to_formatted = null;

    public ?int $booking_product_event_ticket_id = null;

    public ?array $order = null;

    public ?array $order_item = null;

    public ?string $payment_method = null;

    public ?string $payment_title = null;

    public ?string $shipping_method = null;

    public ?string $shipping_title = null;

    public ?array $billing_address = null;

    public ?array $shipping_address = null;

    public ?array $invoices = null;

    public ?array $shipments = null;

    public ?array $refunds = null;

    public ?string $created_at = null;
}

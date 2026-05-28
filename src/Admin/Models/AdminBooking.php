<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\Dto\AdminBookingDetailDto;
use Webkul\BagistoApi\Admin\State\AdminBookingCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminBookingItemProvider;

/**
 * Admin Booking resource (read-only).
 *
 * Lists rows from the `bookings` table (one per booking line on an order),
 * with the linked order/order_item summary and booking-product sub-type
 * (default/appointment/event/rental/table) in the detail payload.
 *
 * REST  : GET /api/admin/bookings              (datagrid listing)
 *         GET /api/admin/bookings/{id}         (detail)
 * GraphQL: adminBookings cursor + adminBooking(id:).
 */
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
                tags: ['Admin Sales'],
                summary: 'List bookings (datagrid parity)',
                description: 'Paginated bookings listing mirroring the admin Sales → Bookings datagrid. Returns a `{ data, meta }` envelope. Requires `sales.bookings.view` permission.',
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
                                    'data' => [[
                                        'id'               => 1,
                                        'orderId'          => 8,
                                        'orderIncrementId' => '00000000008',
                                        'orderItemId'      => 42,
                                        'productId'        => 99,
                                        'productSku'       => 'BK-EVENT-01',
                                        'productName'      => null,
                                        'qty'              => 2,
                                        'from'             => 1716220800,
                                        'to'               => 1716224400,
                                        'fromFormatted'    => '20 May, 2026 12:00PM',
                                        'toFormatted'      => '20 May, 2026 13:00PM',
                                        'createdAt'        => '2026-05-20 10:00:00',
                                    ]],
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
            output: AdminBookingDetailDto::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales'],
                summary: 'Get a booking by id',
                parameters: [
                    new Model\Parameter('id', 'path', 'Booking row ID', true, schema: ['type' => 'integer']),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            provider: AdminBookingItemProvider::class,
            output: AdminBookingDetailDto::class,
            description: 'Get a booking by id.',
        ),
        new QueryCollection(
            provider: AdminBookingCollectionProvider::class,
            paginationType: 'cursor',
            description: 'Admin bookings listing (cursor pagination).',
            extraArgs: [
                'id'              => ['type' => 'String'],
                'order_id'        => ['type' => 'String'],
                'qty'             => ['type' => 'Int'],
                'product_id'      => ['type' => 'Int'],
                'from_from'       => ['type' => 'String'],
                'from_to'         => ['type' => 'String'],
                'to_from'         => ['type' => 'String'],
                'to_to'           => ['type' => 'String'],
                'created_at_from' => ['type' => 'String'],
                'created_at_to'   => ['type' => 'String'],
                'sort'            => ['type' => 'String'],
                'order'           => ['type' => 'String'],
            ],
        ),
    ],
)]
class AdminBooking
{
    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;
}

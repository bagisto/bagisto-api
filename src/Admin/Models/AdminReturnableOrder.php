<?php

namespace Webkul\BagistoApi\Admin\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\OpenApi\Model;
use Webkul\BagistoApi\Admin\State\AdminReturnableOrderProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminReturnableOrder',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/rma/requests/orders',
            provider: AdminReturnableOrderProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: RMA'],
                summary: 'List the orders a return can be opened for (RMA create form)',
                description: 'The order picker of the admin RMA create form (OrderRMADataGrid parity): orders that are not canceled, closed, fraud or pending payment and still hold an item inside its return window with quantity not yet put on a return. Filters: id, increment_id, customer_name, status, method_title, grand_total_from/grand_total_to, created_at_from/created_at_to. Sort: id (default desc), increment_id, customer_name, created_at, grand_total, method_title, status. Returns a `{ data, meta }` envelope. Permission: sales.rma.requests.create.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer']),
                    new Model\Parameter('id', 'query', 'Filter by order id.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('increment_id', 'query', 'Partial order increment id match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('customer_name', 'query', 'Partial customer name match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('status', 'query', 'Filter by order status.', false, schema: ['type' => 'string']),
                    new Model\Parameter('method_title', 'query', 'Filter by payment method title.', false, schema: ['type' => 'string']),
                    new Model\Parameter('grand_total_from', 'query', 'Minimum grand total.', false, schema: ['type' => 'number']),
                    new Model\Parameter('grand_total_to', 'query', 'Maximum grand total.', false, schema: ['type' => 'number']),
                    new Model\Parameter('created_at_from', 'query', 'Orders placed on or after this date (Y-m-d).', false, schema: ['type' => 'string']),
                    new Model\Parameter('created_at_to', 'query', 'Orders placed on or before this date (Y-m-d).', false, schema: ['type' => 'string']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string']),
                    new Model\Parameter('order', 'query', 'Sort direction (asc or desc).', false, schema: ['type' => 'string']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'The orders a return can be opened for.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [
                                        [
                                            'id' => 45,
                                            'incrementId' => '000000045',
                                            'status' => 'completed',
                                            'customerName' => 'Jane Doe',
                                            'isGuest' => false,
                                            'grandTotal' => 120,
                                            'formattedGrandTotal' => '$120.00',
                                            'orderCurrencyCode' => 'USD',
                                            'methodTitle' => 'Cash On Delivery',
                                            'totalQtyOrdered' => 2,
                                            'totalRmaQty' => 0,
                                            'returnableQty' => 2,
                                            'createdAt' => '2026-07-18T09:00:00+00:00',
                                        ],
                                    ],

                                    'meta' => [
                                        'currentPage' => 1,
                                        'perPage' => 10,
                                        'lastPage' => 1,
                                        'total' => 1,
                                        'from' => 1,
                                        'to' => 1,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks sales.rma.requests.create.'),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new QueryCollection(
            provider: AdminReturnableOrderProvider::class,
            paginationType: 'cursor',
            args: [
                'id' => ['type' => 'Int'],
                'increment_id' => ['type' => 'String'],
                'customer_name' => ['type' => 'String'],
                'status' => ['type' => 'String'],
                'method_title' => ['type' => 'String'],
                'grand_total_from' => ['type' => 'Float'],
                'grand_total_to' => ['type' => 'Float'],
                'created_at_from' => ['type' => 'String'],
                'created_at_to' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
                'first' => ['type' => 'Int'],
                'after' => ['type' => 'String'],
            ],
        ),
    ],
)]
class AdminReturnableOrder
{
    /**
     * The order id.
     */
    #[ApiProperty(identifier: true)]
    public ?int $id = null;

    /**
     * The order increment id.
     */
    public ?string $increment_id = null;

    /**
     * The order status.
     */
    public ?string $status = null;

    /**
     * The customer name.
     */
    public ?string $customer_name = null;

    /**
     * Whether the order was placed by a guest.
     */
    public ?bool $is_guest = null;

    /**
     * The grand total in the order currency.
     */
    public ?float $grand_total = null;

    /**
     * The grand total formatted in the order currency.
     */
    public ?string $formatted_grand_total = null;

    /**
     * The order currency code.
     */
    public ?string $order_currency_code = null;

    /**
     * The payment method title.
     */
    public ?string $method_title = null;

    /**
     * The quantity ordered across the eligible items.
     */
    public ?int $total_qty_ordered = null;

    /**
     * The quantity already put on a return across the eligible items.
     */
    public ?int $total_rma_qty = null;

    /**
     * The quantity still available for a return.
     */
    public ?int $returnable_qty = null;

    /**
     * The order creation time.
     */
    public ?string $created_at = null;
}

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
use Webkul\BagistoApi\Admin\Dto\AdminShipmentCreateInput;
use Webkul\BagistoApi\Admin\Dto\Concerns\AcceptsCamelCaseWrites;
use Webkul\BagistoApi\Admin\State\AdminShipmentCollectionProvider;
use Webkul\BagistoApi\Admin\State\AdminShipmentCreateProcessor;
use Webkul\BagistoApi\Admin\State\AdminShipmentExportProvider;
use Webkul\BagistoApi\Admin\State\AdminShipmentProvider;

#[ApiResource(
    routePrefix: '/api/admin',
    shortName: 'AdminShipment',
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/shipments',
            provider: AdminShipmentCollectionProvider::class,
            paginationEnabled: false,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Shipments'],
                summary: 'List shipments (datagrid parity)',
                description: 'Paginated shipments listing mirroring the admin Sales → Shipments datagrid. Every shipment column plus the order/customer context and billing/shipping addresses is populated on each row (line items are detail-only). Returns a `{ data, meta }` envelope. Requires `sales.shipments.view` permission.',
                parameters: [
                    new Model\Parameter('page', 'query', 'Page number (1-based).', false, schema: ['type' => 'integer', 'example' => 1]),
                    new Model\Parameter('per_page', 'query', 'Items per page (default 10, max 50).', false, schema: ['type' => 'integer', 'example' => 10]),
                    new Model\Parameter('id', 'query', 'Filter by shipment id (integer or comma-list).', false, schema: ['type' => 'string', 'example' => '7']),
                    new Model\Parameter('order_id', 'query', 'Partial order increment_id match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('total_qty', 'query', 'Exact total quantity.', false, schema: ['type' => 'integer']),
                    new Model\Parameter('inventory_source_name', 'query', 'Partial inventory source name.', false, schema: ['type' => 'string']),
                    new Model\Parameter('shipped_to', 'query', 'Partial shipped-to name match.', false, schema: ['type' => 'string']),
                    new Model\Parameter('order_date_from', 'query', 'Order created after.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('order_date_to', 'query', 'Order created before.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('created_at_from', 'query', 'Shipment created after.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('created_at_to', 'query', 'Shipment created before.', false, schema: ['type' => 'string', 'format' => 'date']),
                    new Model\Parameter('sort', 'query', 'Sort column.', false, schema: ['type' => 'string', 'enum' => ['id', 'order_id', 'total_qty', 'inventory_source_name', 'shipped_to', 'order_date', 'created_at']]),
                    new Model\Parameter('order', 'query', 'Sort direction.', false, schema: ['type' => 'string', 'enum' => ['asc', 'desc']]),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'Paginated list of shipments in the { data, meta } envelope.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    'data' => [self::SAMPLE_LIST],
                                    'meta' => ['currentPage' => 1, 'perPage' => 10, 'lastPage' => 1, 'total' => 1, 'from' => 1, 'to' => 1],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
        new Get(
            uriTemplate: '/shipments/{id}',
            requirements: ['id' => '\\d+'],
            provider: AdminShipmentProvider::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Shipments'],
                summary: 'Get shipment detail',
                description: 'Returns a single shipment — every column, the order/customer context, billing & shipping addresses, the inventory source, and the shipped line items. Requires `sales.shipments.view` permission.',
                parameters: [
                    new Model\Parameter('id', 'path', 'Shipment ID', true, schema: ['type' => 'integer']),
                ],
                responses: [
                    '200' => new Model\Response(
                        description: 'The shipment.',
                        content: new \ArrayObject([
                            'application/json' => ['example' => self::SAMPLE_DETAIL],
                        ]),
                    ),
                    '404' => new Model\Response(description: 'Unknown shipment id.'),
                    '401' => new Model\Response(description: 'Missing or invalid admin token.'),
                    '403' => new Model\Response(description: 'Admin role lacks sales.shipments.view.'),
                ],
            ),
        ),
        new Post(
            uriTemplate: '/orders/{orderId}/shipments',
            input: AdminShipmentCreateInput::class,
            processor: AdminShipmentCreateProcessor::class,
            openapi: new Model\Operation(
                tags: ['Admin Sales: Orders'],
                summary: 'Create a shipment for an order',
                parameters: [
                    new Model\Parameter('orderId', 'path', 'Order ID', true, schema: ['type' => 'integer']),
                ],
                requestBody: new Model\RequestBody(
                    required: true,
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => ['type' => 'object'],
                            'example' => [
                                'source' => 1,
                                'items' => [
                                    ['orderItemId' => 42, 'inventorySourceId' => 1, 'quantity' => 3],
                                ],
                                'carrierTitle' => 'UPS',
                                'trackNumber' => '1Z999AA1',
                            ],
                        ],
                    ]),
                ),
            ),
        ),
        new Get(
            uriTemplate: '/shipments/export',
            provider: AdminShipmentExportProvider::class,
            outputFormats: ['csv' => ['text/csv']],
            openapi: new Model\Operation(
                tags: ['Admin Sales: Shipments'],
                summary: 'Export shipments as CSV',
                description: 'Downloads the shipments datagrid as a CSV file (text/csv attachment) — the same data the admin Export button produces. Honours the same filters as the listing. Binary download, not JSON. Only ?format=csv is supported.',
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
            provider: AdminShipmentProvider::class,
        ),
        new QueryCollection(
            provider: AdminShipmentCollectionProvider::class,
            paginationType: 'cursor',
            description: 'Admin shipments datagrid listing (cursor pagination).',
            extraArgs: [
                'id' => ['type' => 'String'],
                'order_id' => ['type' => 'String'],
                'total_qty' => ['type' => 'Int'],
                'inventory_source_name' => ['type' => 'String'],
                'shipped_to' => ['type' => 'String'],
                'order_date_from' => ['type' => 'String'],
                'order_date_to' => ['type' => 'String'],
                'created_at_from' => ['type' => 'String'],
                'created_at_to' => ['type' => 'String'],
                'sort' => ['type' => 'String'],
                'order' => ['type' => 'String'],
            ],
        ),
        new Mutation(
            name: 'create',
            input: AdminShipmentCreateInput::class,
            processor: AdminShipmentCreateProcessor::class,
        ),
    ],
)]
class AdminShipment
{
    use AcceptsCamelCaseWrites;

    private const SAMPLE_LIST = [
        'id' => 7,
        'orderId' => 8,
        'orderIncrementId' => '00000000008',
        'shippedTo' => 'John Doe',
        'orderDate' => '2026-05-20 10:00:00',
        'orderStatus' => 'processing',
        'orderStatusLabel' => 'Processing',
        'channelName' => 'Default',
        'customerName' => 'John Doe',
        'customerEmail' => 'john.doe@example.com',
        'paymentMethod' => 'cashondelivery',
        'paymentTitle' => 'Cash On Delivery',
        'orderCurrencyCode' => 'USD',
        'shippingMethod' => 'flatrate_flatrate',
        'shippingTitle' => 'Flat Rate - Flat Rate',
        'baseShippingAmount' => 30,
        'formattedBaseShippingAmount' => '$30.00',
        'status' => null,
        'totalQty' => 2,
        'totalWeight' => null,
        'carrierCode' => null,
        'carrierTitle' => 'UPS',
        'trackNumber' => '1Z999AA1',
        'emailSent' => false,
        'inventorySourceId' => 1,
        'inventorySourceName' => 'Default',
        'billingAddress' => self::SAMPLE_BILLING,
        'shippingAddress' => self::SAMPLE_SHIPPING,
        'createdAt' => '2026-05-20 12:00:00',
        'updatedAt' => '2026-05-20 12:00:00',
        'items' => [],
    ];

    private const SAMPLE_DETAIL = [
        'id' => 7,
        'orderId' => 8,
        'orderIncrementId' => '00000000008',
        'shippedTo' => 'John Doe',
        'orderDate' => '2026-05-20 10:00:00',
        'orderStatus' => 'processing',
        'orderStatusLabel' => 'Processing',
        'channelName' => 'Default',
        'customerName' => 'John Doe',
        'customerEmail' => 'john.doe@example.com',
        'paymentMethod' => 'cashondelivery',
        'paymentTitle' => 'Cash On Delivery',
        'orderCurrencyCode' => 'USD',
        'shippingMethod' => 'flatrate_flatrate',
        'shippingTitle' => 'Flat Rate - Flat Rate',
        'baseShippingAmount' => 30,
        'formattedBaseShippingAmount' => '$30.00',
        'status' => null,
        'totalQty' => 2,
        'totalWeight' => null,
        'carrierCode' => null,
        'carrierTitle' => 'UPS',
        'trackNumber' => '1Z999AA1',
        'emailSent' => false,
        'inventorySourceId' => 1,
        'inventorySourceName' => 'Default',
        'billingAddress' => self::SAMPLE_BILLING,
        'shippingAddress' => self::SAMPLE_SHIPPING,
        'createdAt' => '2026-05-20 12:00:00',
        'updatedAt' => '2026-05-20 12:00:00',
        'items' => [[
            'id' => 11,
            'orderItemId' => 42,
            'sku' => 'TSHIRT-RED-M',
            'name' => 'Red T-Shirt',
            'qty' => 2,
        ]],
    ];

    private const SAMPLE_BILLING = [
        'id' => 16,
        'addressType' => 'order_billing',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'city' => 'Los Angeles',
        'country' => 'US',
        'postcode' => '90001',
        'email' => 'john.doe@example.com',
        'phone' => '5551234567',
    ];

    private const SAMPLE_SHIPPING = [
        'id' => 15,
        'addressType' => 'order_shipping',
        'firstName' => 'John',
        'lastName' => 'Doe',
        'city' => 'Los Angeles',
        'country' => 'US',
        'postcode' => '90001',
        'email' => 'john.doe@example.com',
        'phone' => '5551234567',
    ];

    #[ApiProperty(identifier: true, writable: false)]
    public ?int $id = null;

    public ?int $order_id = null;

    public ?string $order_increment_id = null;

    public ?string $shipped_to = null;

    public ?string $order_date = null;

    public ?string $order_status = null;

    public ?string $order_status_label = null;

    public ?string $channel_name = null;

    public ?string $customer_name = null;

    public ?string $customer_email = null;

    public ?string $payment_method = null;

    public ?string $payment_title = null;

    public ?string $order_currency_code = null;

    public ?string $shipping_method = null;

    public ?string $shipping_title = null;

    public ?float $base_shipping_amount = null;

    public ?string $formatted_base_shipping_amount = null;

    public ?string $status = null;

    public ?int $total_qty = null;

    public ?float $total_weight = null;

    public ?string $carrier_code = null;

    public ?string $carrier_title = null;

    public ?string $track_number = null;

    public ?bool $email_sent = null;

    public ?int $inventory_source_id = null;

    public ?string $inventory_source_name = null;

    public ?array $billing_address = null;

    public ?array $shipping_address = null;

    public ?string $created_at = null;

    public ?string $updated_at = null;

    public array $items = [];
}
